<?php
require_once __DIR__ . '/../includes/auth.php';
require_user();
$user = current_user();

// Featured cars (3 available)
$featured = $pdo->query(
    "SELECT * FROM cars WHERE status = 'available' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

// Recent bookings for this user (5)
$stmt = $pdo->prepare("
    SELECT b.*, c.name AS car_name, c.license_plate, c.type AS car_type, c.seats
    FROM bookings b
    JOIN cars c ON c.id = b.car_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$recent = $stmt->fetchAll();

$totalCars      = (int) $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$availableCars  = (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE status='available'")->fetchColumn();

$pageTitle      = 'Dashboard';
$transparentNav = true;
$currentPage    = 'dashboard';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main>
  <!-- ═══ HERO ═══════════════════════════════════════════════════ -->
  <section class="relative overflow-hidden bg-stone-950 min-h-[90vh] flex items-center">
    <div class="absolute inset-0" id="hero-parallax">
      <img src="<?= e(url('/assets/images/corporate-hero.png')) ?>" alt="Corporate fleet"
           class="absolute inset-0 h-full w-full object-cover object-center scale-110">
      <div class="absolute inset-0 bg-stone-950/65"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-stone-950/30 via-transparent to-stone-950/85"></div>
    </div>
    <div class="relative mx-auto w-full max-w-7xl px-6 py-28 sm:py-36 lg:px-10 lg:py-44">
      <div class="mx-auto max-w-3xl text-center">
        <p class="eyebrow mb-5 text-copper-400 animate-fade-up" style="animation-delay:0.05s">AutoBook — Internal Fleet System</p>
        <h1 class="display-serif text-[44px] leading-[1.04] text-white sm:text-[70px] lg:text-[84px] animate-fade-up" style="animation-delay:0.12s">
          Reserve your<br>
          <span class="italic text-stone-300">next journey.</span>
        </h1>
        <p class="mx-auto mt-7 max-w-xl text-[16px] sm:text-[18px] leading-relaxed text-stone-300 animate-fade-up" style="animation-delay:0.22s">
          ค้นหาและจองรถยนต์ขององค์กรได้ง่ายในไม่กี่ขั้นตอน พร้อมตรวจสอบสถานะการอนุมัติและประวัติการใช้งานได้แบบเรียลไทม์
        </p>

        <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row animate-fade-up" style="animation-delay:0.32s">
          <a href="<?= e(url('/user/cars.php')) ?>" data-magnetic
             class="magnetic w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-md bg-white px-9 py-4 text-sm font-semibold text-stone-900 shadow-xl hover:bg-stone-100 transition-colors">
            Browse Vehicles
            <i data-lucide="arrow-right" style="width:16px;height:16px"></i>
          </a>
          <a href="<?= e(url('/user/my-bookings.php')) ?>" data-magnetic
             class="magnetic w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-md border border-white/20 bg-white/5 px-9 py-4 text-sm font-medium text-white backdrop-blur-md hover:bg-white/12 transition-colors">
            My Bookings
          </a>
        </div>

        <!-- Mini stats strip -->
        <div class="mt-16 flex justify-center gap-10 sm:gap-16 animate-fade-up" style="animation-delay:0.42s">
          <div class="text-center">
            <p class="display-serif text-3xl text-white tabular-nums" data-count="<?= $totalCars ?>">0</p>
            <p class="eyebrow mt-1 text-stone-500">รถทั้งหมด</p>
          </div>
          <div class="w-px bg-white/10"></div>
          <div class="text-center">
            <p class="display-serif text-3xl text-white tabular-nums" data-count="<?= $availableCars ?>">0</p>
            <p class="eyebrow mt-1 text-stone-500">พร้อมจอง</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ HOW IT WORKS ═══════════════════════════════════════════ -->
  <section class="border-b border-stone-100 bg-white">
    <div class="mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
      <div class="mb-14 reveal-left">
        <p class="eyebrow mb-2">Simple Process</p>
        <h2 class="display-serif heading-underline text-[36px] leading-tight text-stone-900">วิธีการจองรถ</h2>
        <div class="mt-3 h-px w-16 bg-copper-500 line-draw" style="--delay:120ms"></div>
      </div>

      <div class="grid grid-cols-1 gap-0 sm:grid-cols-3" data-stagger="100">
        <?php
        $steps = [
          ['01', 'เลือกรถ',    'เรียกดูรถที่พร้อมใช้งานในระบบ เลือกตามประเภทและจำนวนที่นั่งที่ต้องการ'],
          ['02', 'กรอกคำขอ',   'ระบุวันเริ่มต้น วันสิ้นสุด ปลายทาง และเหตุผลการใช้รถ'],
          ['03', 'รออนุมัติ',   'Admin ตรวจสอบและแจ้งผลการอนุมัติ คุณสามารถติดตามสถานะได้ตลอดเวลา'],
        ];
        foreach ($steps as $i => [$num, $title, $desc]): ?>
          <div class="reveal flex flex-col gap-4 p-8 <?= $i !== 0 ? 'border-t sm:border-t-0 sm:border-l border-stone-100' : '' ?>">
            <div class="flex items-center gap-4">
              <span class="section-number text-5xl text-stone-200"><?= e($num) ?></span>
              <div class="h-px flex-1 bg-stone-100"></div>
            </div>
            <h3 class="text-lg font-semibold text-stone-900"><?= e($title) ?></h3>
            <p class="text-sm leading-relaxed text-stone-500"><?= e($desc) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ═══ AVAILABLE VEHICLES ══════════════════════════════════════ -->
  <?php if (!empty($featured)): ?>
  <section class="bg-stone-50">
    <div class="mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
      <div class="mb-12 flex items-end justify-between gap-4">
        <div class="reveal-left">
          <p class="eyebrow mb-2">Ready Now</p>
          <div class="mt-3 h-px w-12 bg-copper-500 line-draw" style="--delay:100ms"></div>
        </div>
        <a href="<?= e(url('/user/cars.php')) ?>" class="reveal-right inline-flex items-center gap-1.5 text-sm font-medium text-stone-900 underline-offset-4 hover:underline">
          ดูทั้งหมด <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
        </a>
      </div>

      <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3" data-stagger="80">
        <?php foreach ($featured as $car): ?>
          <div class="reveal-scale">
            <?php include __DIR__ . '/_car_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ═══ RECENT BOOKINGS ═════════════════════════════════════════ -->
  <section class="bg-white">
    <div class="mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
      <div class="mb-10 flex items-end justify-between gap-4">
        <div class="reveal-left">
          <p class="eyebrow mb-2">Activity</p>
          <h2 class="display-serif text-[36px] leading-tight text-stone-900">การจองล่าสุด</h2>
          <div class="mt-3 h-px w-12 bg-copper-500 line-draw" style="--delay:100ms"></div>
        </div>
        <?php if (!empty($recent)): ?>
          <a href="<?= e(url('/user/my-bookings.php')) ?>" class="reveal-right inline-flex items-center gap-1.5 text-sm font-medium text-stone-900 underline-offset-4 hover:underline">
            ดูทั้งหมด <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
          </a>
        <?php endif; ?>
      </div>

      <?php if (empty($recent)): ?>
        <div class="reveal rounded-xl border border-stone-200 bg-stone-50 py-16 text-center">
          <i data-lucide="clock" class="mx-auto mb-4 text-stone-300" style="width:32px;height:32px"></i>
          <p class="text-base font-medium text-stone-500">ยังไม่มีประวัติการจอง</p>
          <p class="mt-1 text-sm text-stone-400">เริ่มต้นจองรถคันแรกของคุณได้เลย</p>
          <a href="<?= e(url('/user/cars.php')) ?>" class="btn-primary mt-6 px-6 py-2.5">
            Browse Vehicles <i data-lucide="arrow-right" class="ml-1.5" style="width:14px;height:14px"></i>
          </a>
        </div>
      <?php else: ?>
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
          <ul class="divide-y divide-stone-100" data-stagger="60">
            <?php foreach ($recent as $b):
              $start = thai_date($b['start_date']);
              $end   = thai_date($b['end_date']);
            ?>
              <li class="reveal flex flex-col gap-3 px-6 py-5 transition-colors hover:bg-stone-50/60 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-5">
                  <div class="flex h-12 w-12 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-stone-100 text-center transition-colors hover:bg-stone-200">
                    <span class="display-serif text-xl leading-none text-stone-900"><?= e($start['day']) ?></span>
                    <span class="text-[9px] uppercase tracking-wider text-stone-500"><?= e($start['month']) ?></span>
                  </div>
                  <div>
                    <p class="font-semibold text-stone-900"><?= e($b['car_name']) ?></p>
                    <p class="mt-0.5 text-sm text-stone-500">
                      <?= e($b['destination']) ?>
                      <span class="mx-1.5 text-stone-300">·</span>
                      <span class="font-mono text-xs"><?= e($b['license_plate']) ?></span>
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-4 pl-17 sm:pl-0">
                  <span class="text-xs text-stone-400"><?= e($start['date']) ?> → <?= e($end['date']) ?></span>
                  <?= status_badge($b['status']) ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<!-- Footer -->
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
