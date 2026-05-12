<?php
require_once __DIR__ . '/../includes/auth.php';
require_user();
$user = current_user();

$filter = (string) ($_GET['status'] ?? '');
$valid  = ['pending', 'approved', 'rejected', 'completed'];
$where  = 'b.user_id = ?';
$params = [$user['id']];
if (in_array($filter, $valid, true)) {
    $where  .= ' AND b.status = ?';
    $params[] = $filter;
}

$sql = "SELECT b.*, c.name AS car_name, c.license_plate, c.type AS car_type, c.seats
        FROM bookings b
        JOIN cars c ON c.id = b.car_id
        WHERE $where
        ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$statusFilters = [
    ['',           'All'],
    ['pending',    'Pending'],
    ['approved',   'Approved'],
    ['rejected',   'Rejected'],
    ['completed',  'Completed'],
];

$pageTitle   = 'My Bookings';
$currentPage = 'my-bookings';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="pt-14">
  <!-- ─── PAGE HEADER ─────────────────────────────── -->
  <div class="border-b border-stone-200 bg-white">
    <div class="mx-auto max-w-7xl px-6 py-12 lg:px-10 lg:py-16">
      <p class="eyebrow mb-2 animate-fade-up" style="animation-delay:0.05s">Activity</p>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="animate-fade-up" style="animation-delay:0.12s">
          <h1 class="display-serif text-[40px] leading-tight text-stone-900 sm:text-[52px]">My Bookings</h1>
          <div class="mt-2 h-px w-14 bg-copper-500 line-draw" style="--delay:180ms"></div>
          <p class="mt-3 text-sm text-stone-500">ติดตามสถานะคำขอจองและประวัติการใช้รถของคุณ</p>
        </div>
        <div class="flex flex-wrap gap-2 animate-fade-up" style="animation-delay:0.22s" data-stagger="40">
          <?php foreach ($statusFilters as [$v, $l]):
            $active = ($filter === $v);
            $href = $v === '' ? url('/user/my-bookings.php') : url('/user/my-bookings.php?status=' . $v);
          ?>
            <a href="<?= e($href) ?>"
               class="rounded-full border px-4 py-1.5 text-xs font-medium transition-all duration-200 hover:-translate-y-px <?= $active
                  ? 'border-stone-900 bg-stone-900 text-white'
                  : 'border-stone-200 text-stone-600 hover:border-stone-400 bg-white' ?>">
              <?= e($l) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── LIST ────────────────────────────────────── -->
  <div class="mx-auto max-w-7xl px-6 py-10 lg:px-10 lg:py-14">
    <?php if (empty($bookings)): ?>
      <div class="reveal rounded-xl border border-stone-200 bg-stone-50 py-20 text-center">
        <i data-lucide="calendar-days" class="mx-auto mb-4 text-stone-300" style="width:36px;height:36px"></i>
        <p class="text-base font-semibold text-stone-500">ไม่พบรายการจอง</p>
        <p class="mt-1 text-sm text-stone-400">
          <?= $filter ? 'ลองเลือกสถานะอื่นดู' : 'เริ่มต้นจองรถคันแรกของคุณได้เลย' ?>
        </p>
        <?php if (!$filter): ?>
          <a href="<?= e(url('/user/cars.php')) ?>" class="btn-primary mt-6 inline-flex items-center gap-2 px-6 py-2.5">
            Browse Vehicles <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="space-y-4" data-stagger="65">
        <?php foreach ($bookings as $b):
          $start = thai_date($b['start_date']);
          $end   = thai_date($b['end_date']);
          $days  = date_range_days($b['start_date'], $b['end_date']);
          $accent = [
            'approved'  => 'bg-blue-500',
            'pending'   => 'bg-amber-400',
            'rejected'  => 'bg-rose-500',
            'completed' => 'bg-stone-300',
          ][$b['status']] ?? 'bg-stone-200';
        ?>
          <div class="reveal overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm card-hover">
            <div class="flex flex-col gap-0 sm:flex-row">
              <div class="w-full sm:w-1.5 flex-shrink-0 <?= $accent ?> sm:rounded-l-xl min-h-[4px] sm:min-h-0"></div>
              <div class="flex flex-1 flex-col gap-5 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                  <div class="flex-shrink-0">
                    <div class="flex flex-col items-center rounded-lg border border-stone-200 overflow-hidden w-14">
                      <div class="w-full bg-stone-900 py-0.5 text-center text-[9px] font-bold uppercase tracking-wider text-white">
                        <?= e($start['month']) ?>
                      </div>
                      <span class="display-serif py-1.5 text-2xl leading-none text-stone-900"><?= e($start['day']) ?></span>
                    </div>
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <h3 class="font-semibold text-stone-900"><?= e($b['car_name']) ?></h3>
                      <span class="font-mono text-xs text-stone-400"><?= e($b['license_plate']) ?></span>
                    </div>
                    <div class="mt-1.5 flex flex-wrap items-center gap-3 text-sm text-stone-500">
                      <span class="flex items-center gap-1">
                        <i data-lucide="map-pin" class="text-stone-400" style="width:12px;height:12px"></i>
                        <?= e($b['destination']) ?>
                      </span>
                      <span class="flex items-center gap-1">
                        <i data-lucide="calendar-days" class="text-stone-400" style="width:12px;height:12px"></i>
                        <?= e($start['date']) ?> → <?= e($end['date']) ?>
                        <span class="text-stone-400">(<?= (int) $days ?> วัน)</span>
                      </span>
                      <span class="flex items-center gap-1">
                        <i data-lucide="car" class="text-stone-400" style="width:12px;height:12px"></i>
                        <?= e($b['car_type']) ?> · <?= (int) $b['seats'] ?> ที่นั่ง
                      </span>
                    </div>
                    <?php if (!empty($b['admin_note'])): ?>
                      <p class="mt-2 text-xs italic text-stone-400">Admin: "<?= e($b['admin_note']) ?>"</p>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="flex flex-shrink-0 items-center sm:flex-col sm:items-end gap-2">
                  <?= status_badge($b['status']) ?>
                  <span class="text-[10px] text-stone-400 tabular-nums">#<?= e(str_pad((string) $b['id'], 8, '0', STR_PAD_LEFT)) ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
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

<?php require __DIR__ . '/../includes/footer.php'; ?>
