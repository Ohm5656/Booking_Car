<?php
require_once __DIR__ . '/../includes/auth.php';
require_user();
$user = current_user();

$carId = (int) ($_GET['car_id'] ?? $_POST['car_id'] ?? 0);
if ($carId <= 0) {
    flash('error', 'ไม่พบรถที่เลือก');
    header('Location: ' . url('/user/cars.php'));
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ? LIMIT 1');
$stmt->execute([$carId]);
$car = $stmt->fetch();
if (!$car) {
    flash('error', 'ไม่พบรถที่เลือก');
    header('Location: ' . url('/user/cars.php'));
    exit;
}

// Load existing blocked ranges for this car (pending + approved)
$blkStmt = $pdo->prepare(
    "SELECT start_date, end_date FROM bookings
     WHERE car_id = ? AND status IN ('pending','approved')
     ORDER BY start_date ASC"
);
$blkStmt->execute([$carId]);
$blockedRanges = $blkStmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$form = [
    'startDate'   => '',
    'endDate'     => '',
    'destination' => '',
    'reason'      => '',
    'phone'       => $user['phone'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['startDate']   = trim((string) ($_POST['startDate']   ?? ''));
    $form['endDate']     = trim((string) ($_POST['endDate']     ?? ''));
    $form['destination'] = trim((string) ($_POST['destination'] ?? ''));
    $form['reason']      = trim((string) ($_POST['reason']      ?? ''));
    $form['phone']       = trim((string) ($_POST['phone']       ?? ''));

    if ($form['startDate'] === '' || $form['endDate'] === '' || $form['destination'] === '' || $form['reason'] === '' || $form['phone'] === '') {
        $errors[] = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    } elseif (strtotime($form['startDate']) === false || strtotime($form['endDate']) === false) {
        $errors[] = 'รูปแบบวันที่ไม่ถูกต้อง';
    } elseif (strtotime($form['startDate']) > strtotime($form['endDate'])) {
        $errors[] = 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น';
    }

    if (empty($errors) && has_overlap($pdo, (int) $car['id'], $form['startDate'], $form['endDate'])) {
        $blk = $pdo->prepare(
            "SELECT start_date, end_date FROM bookings
             WHERE car_id = ? AND status IN ('pending','approved')
               AND start_date <= ? AND end_date >= ?
             ORDER BY start_date LIMIT 1"
        );
        $blk->execute([$car['id'], $form['endDate'], $form['startDate']]);
        $conflict = $blk->fetch();
        if ($conflict) {
            $cs = thai_date($conflict['start_date']);
            $ce = thai_date($conflict['end_date']);
            $errors[] = 'รถคันนี้มีการจองในช่วง ' . $cs['long'] . ' – ' . $ce['long'] . ' อยู่แล้ว กรุณาเลือกช่วงวันที่อื่น';
        } else {
            $errors[] = 'รถคันนี้ถูกจองในช่วงวันที่เลือกแล้ว กรุณาเลือกช่วงวันที่อื่น';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO bookings (user_id, car_id, start_date, end_date, destination, reason, phone, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([
            $user['id'], $car['id'],
            $form['startDate'], $form['endDate'],
            $form['destination'], $form['reason'], $form['phone'],
        ]);
        flash('success', 'ส่งคำขอจองแล้ว');
        header('Location: ' . url('/user/my-bookings.php'));
        exit;
    }
}

$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Prepare blocked ranges JSON for JS (ISO dates compare correctly as strings)
$blockedJson = json_encode(
    array_map(fn($r) => ['s' => $r['start_date'], 'e' => $r['end_date']], $blockedRanges),
    JSON_UNESCAPED_UNICODE
);

$pageTitle   = 'Book Vehicle';
$currentPage = 'cars';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="pt-14">
  <div class="border-b border-stone-200 bg-white">
    <div class="mx-auto max-w-7xl px-6 py-12 lg:px-10 lg:py-16">
      <p class="eyebrow mb-2 animate-fade-up" style="animation-delay:0.05s">New Request</p>
      <h1 class="display-serif text-[40px] leading-tight text-stone-900 sm:text-[52px] animate-fade-up" style="animation-delay:0.12s">ส่งคำขอจองรถ</h1>
      <div class="mt-2 h-px w-14 bg-copper-500 animate-fade-up" style="animation-delay:0.2s"></div>
      <p class="mt-3 text-sm text-stone-500 animate-fade-up" style="animation-delay:0.22s">กรอกข้อมูลให้ครบ จากนั้นรอการอนุมัติจากผู้ดูแลระบบ</p>
    </div>
  </div>

  <div class="mx-auto max-w-3xl px-6 py-10 lg:px-10 lg:py-14">

    <?php if (!empty($errors)): ?>
      <div class="reveal mb-5 flex items-start gap-3 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <i data-lucide="alert-circle" class="mt-0.5 flex-shrink-0" style="width:15px;height:15px"></i>
        <?= e($errors[0]) ?>
      </div>
    <?php endif; ?>

    <div class="reveal-scale surface p-6 sm:p-8">
      <form method="post" id="booking-form" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="car_id" value="<?= (int) $car['id'] ?>">

        <!-- Vehicle info row -->
        <div class="surface-muted flex items-center gap-3 rounded-md px-4 py-3">
          <i data-lucide="car" class="flex-shrink-0 text-stone-400" style="width:18px;height:18px"></i>
          <div>
            <p class="eyebrow mb-0.5">Selected vehicle</p>
            <p class="text-sm font-medium text-stone-900"><?= e($car['name']) ?> (<?= e($car['license_plate']) ?>)</p>
          </div>
        </div>

        <!-- Dates -->
        <div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="startDate" class="mb-1.5 flex items-center gap-2 text-sm font-medium text-stone-700">
                <i data-lucide="calendar-days" class="text-stone-400" style="width:14px;height:14px"></i> วันที่เริ่ม
              </label>
              <input type="date" id="startDate" name="startDate" required
                     min="<?= e($tomorrow) ?>" value="<?= e($form['startDate']) ?>"
                     class="input-modern" autocomplete="off">
            </div>
            <div>
              <label for="endDate" class="mb-1.5 flex items-center gap-2 text-sm font-medium text-stone-700">
                <i data-lucide="calendar-days" class="text-stone-400" style="width:14px;height:14px"></i> วันที่สิ้นสุด
              </label>
              <input type="date" id="endDate" name="endDate" required
                     min="<?= e($form['startDate'] ?: $tomorrow) ?>" value="<?= e($form['endDate']) ?>"
                     class="input-modern" autocomplete="off">
            </div>
          </div>

          <!-- Real-time overlap warning -->
          <div id="overlap-warning" class="hidden mt-3 flex items-start gap-2.5 rounded-md border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-sm text-amber-800">
            <i data-lucide="calendar-x-2" class="mt-0.5 flex-shrink-0" style="width:15px;height:15px"></i>
            <span id="overlap-msg"></span>
          </div>
        </div>

        <!-- Blocked periods for this car -->
        <?php if (!empty($blockedRanges)): ?>
        <div class="rounded-md border border-stone-200 bg-stone-50 px-4 py-3">
          <p class="eyebrow mb-2.5 text-rose-500 flex items-center gap-1.5">
            <i data-lucide="lock" style="width:11px;height:11px"></i>
            ช่วงเวลาที่ถูกจองแล้ว
          </p>
          <ul class="space-y-1.5">
            <?php foreach ($blockedRanges as $r):
              $rs = thai_date($r['start_date']);
              $re = thai_date($r['end_date']);
            ?>
              <li class="flex items-center gap-2 text-xs text-stone-600">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-rose-400 flex-shrink-0"></span>
                <?= e($rs['long']) ?> – <?= e($re['long']) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Destination -->
        <div>
          <label for="destination" class="mb-1.5 flex items-center gap-2 text-sm font-medium text-stone-700">
            <i data-lucide="map-pin" class="text-stone-400" style="width:14px;height:14px"></i> ปลายทาง
          </label>
          <input type="text" id="destination" name="destination" required
                 value="<?= e($form['destination']) ?>" class="input-modern"
                 placeholder="เช่น คณะวิศวกรรมศาสตร์, กรุงเทพฯ">
        </div>

        <!-- Phone -->
        <div>
          <label for="phone" class="mb-1.5 flex items-center gap-2 text-sm font-medium text-stone-700">
            <i data-lucide="phone" class="text-stone-400" style="width:14px;height:14px"></i> เบอร์โทรติดต่อ
          </label>
          <input type="tel" id="phone" name="phone" required
                 value="<?= e($form['phone']) ?>" class="input-modern" placeholder="08x-xxx-xxxx">
        </div>

        <!-- Reason -->
        <div>
          <label for="reason" class="mb-1.5 flex items-center gap-2 text-sm font-medium text-stone-700">
            <i data-lucide="file-text" class="text-stone-400" style="width:14px;height:14px"></i> เหตุผลการใช้รถ
          </label>
          <textarea id="reason" name="reason" rows="3" required
                    class="input-modern resize-none"
                    placeholder="ระบุวัตถุประสงค์การใช้รถ…"><?= e($form['reason']) ?></textarea>
        </div>

        <div class="flex justify-end gap-3 border-t border-stone-200 pt-5">
          <a href="<?= e(url('/user/cars.php')) ?>" class="btn-secondary">ยกเลิก</a>
          <button type="submit" id="submit-btn" class="btn-primary gap-2">
            <i data-lucide="send" style="width:14px;height:14px"></i>
            ส่งคำขอ
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<footer class="border-t border-stone-200 bg-white">
  <div class="mx-auto max-w-7xl px-6 py-8 lg:px-10">
    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
      <div class="flex items-center gap-2.5">
        <span class="display-serif text-base text-stone-700">AutoBook</span>
        <span class="text-stone-300">·</span>
        <span class="text-xs text-stone-400">ระบบจองรถองค์กร</span>
      </div>
      <p class="text-xs text-stone-400">© <?= date('Y') ?> Internal use only</p>
    </div>
  </div>
</footer>

<script>
(function () {
  var sd      = document.getElementById('startDate');
  var ed      = document.getElementById('endDate');
  var warning = document.getElementById('overlap-warning');
  var msg     = document.getElementById('overlap-msg');
  var btn     = document.getElementById('submit-btn');

  // Blocked ranges from PHP (ISO date strings — compare fine as strings)
  var blocked = <?= $blockedJson ?>;

  // Thai month names for the inline message
  var MONTHS = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
  function fmtDate(iso) {
    var p = iso.split('-');
    var y = parseInt(p[0], 10) + 543;
    return parseInt(p[2], 10) + ' ' + MONTHS[parseInt(p[1], 10)] + ' ' + y;
  }

  function checkOverlap() {
    var s = sd.value, e = ed.value;
    if (!s || !e) { clearWarning(); return; }

    var conflict = null;
    for (var i = 0; i < blocked.length; i++) {
      // Standard overlap: s <= b.e && e >= b.s
      if (s <= blocked[i].e && e >= blocked[i].s) { conflict = blocked[i]; break; }
    }

    if (conflict) {
      msg.textContent = 'ช่วงวันที่เลือกชนกับการจองที่มีอยู่แล้ว ('
        + fmtDate(conflict.s) + ' – ' + fmtDate(conflict.e)
        + ') กรุณาเลือกวันอื่น';
      warning.classList.remove('hidden');
      sd.classList.add('border-amber-400');
      ed.classList.add('border-amber-400');
      btn.disabled = true;
      btn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      clearWarning();
    }
  }

  function clearWarning() {
    warning.classList.add('hidden');
    sd.classList.remove('border-amber-400');
    ed.classList.remove('border-amber-400');
    btn.disabled = false;
    btn.classList.remove('opacity-50', 'cursor-not-allowed');
  }

  // Keep end-date min in sync with start-date
  if (sd && ed) {
    sd.addEventListener('change', function () {
      ed.min = sd.value || '<?= e($tomorrow) ?>';
      if (ed.value && ed.value < sd.value) ed.value = '';
      checkOverlap();
    });
    ed.addEventListener('change', checkOverlap);
  }

  // Run once on load in case form has pre-filled values (error re-render)
  if (sd && sd.value && ed && ed.value) checkOverlap();
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
