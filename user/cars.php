<?php
require_once __DIR__ . '/../includes/auth.php';
require_user();
$user = current_user();

$query      = trim((string) ($_GET['q']     ?? ''));
$filter     =        (string) ($_GET['status'] ?? 'all');   // 'all' | 'available' | 'booked'
$typeFilter =        (string) ($_GET['type']   ?? 'all');

$cars   = $pdo->query('SELECT * FROM cars ORDER BY created_at DESC')->fetchAll();
$types  = array_values(array_unique(array_column($cars, 'type')));

$totalAvailable = 0;
foreach ($cars as $c) if ($c['status'] === 'available') $totalAvailable++;

// Apply filters
$filtered = array_values(array_filter($cars, function ($c) use ($filter, $typeFilter, $query) {
    if ($filter !== 'all' && $c['status'] !== $filter) return false;
    if ($typeFilter !== 'all' && $c['type'] !== $typeFilter) return false;
    if ($query !== '') {
        $q = mb_strtolower($query);
        $hay = mb_strtolower($c['name'] . ' ' . $c['license_plate'] . ' ' . $c['type']);
        if (mb_strpos($hay, $q) === false) return false;
    }
    return true;
}));

$hasFilters = ($query !== '' || $filter !== 'all' || $typeFilter !== 'all');

$pageTitle   = 'Vehicles';
$currentPage = 'cars';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';

function build_url(array $overrides = []): string {
    $params = array_merge([
        'q'      => $_GET['q']      ?? '',
        'status' => $_GET['status'] ?? 'all',
        'type'   => $_GET['type']   ?? 'all',
    ], $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== 'all' && $v !== null);
    return url('/user/cars.php') . ($params ? '?' . http_build_query($params) : '');
}
?>

<main class="pt-14">
  <!-- ─── PAGE HEADER ───────────────────────────────── -->
  <div class="border-b border-stone-200 bg-white">
    <div class="mx-auto max-w-7xl px-6 py-12 lg:px-10 lg:py-16">
      <p class="eyebrow mb-2 animate-fade-up" style="animation-delay:0.05s">Fleet Directory</p>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="animate-fade-up" style="animation-delay:0.12s">
          <h1 class="display-serif text-[40px] leading-tight text-stone-900 sm:text-[52px]">All Vehicles</h1>
          <div class="mt-2 h-px w-14 bg-copper-500 line-draw" style="--delay:200ms"></div>
          <p class="mt-3 text-sm text-stone-500">
            <span class="font-semibold text-emerald-600 tabular-nums" data-count="<?= (int) $totalAvailable ?>">0</span>
            รถพร้อมจอง จาก <span class="tabular-nums"><?= count($cars) ?></span> คันทั้งหมด
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── CONTENT ───────────────────────────────────── -->
  <div class="mx-auto max-w-7xl px-6 py-10 lg:px-10 lg:py-14">
    <form method="get" class="grid gap-10 lg:grid-cols-12 lg:gap-12" id="filter-form">

      <!-- ── FILTERS SIDEBAR ── -->
      <aside class="lg:col-span-3 reveal-left">
        <div class="lg:sticky lg:top-24 space-y-6">
          <!-- Search -->
          <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-stone-500">Search</label>
            <div class="relative mt-2">
              <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400" style="width:14px;height:14px"></i>
              <input type="search" name="q" value="<?= e($query) ?>" placeholder="ชื่อรถ, ทะเบียน…"
                     class="input-modern pl-9 text-sm" onchange="document.getElementById('filter-form').submit()">
            </div>
          </div>

          <!-- Status filter -->
          <div class="border-t border-stone-100 pt-6">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 mb-3">Availability</p>
            <div class="space-y-2">
              <?php
              $statusOpts = [
                ['all',       'ทั้งหมด',      count($cars)],
                ['available', 'พร้อมจอง',     $totalAvailable],
                ['booked',    'ไม่ว่าง',       count($cars) - $totalAvailable],
              ];
              foreach ($statusOpts as [$v, $l, $count]):
                $active = ($filter === $v);
              ?>
                <a href="<?= e(build_url(['status' => $v])) ?>"
                   class="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm transition-colors <?= $active ? 'bg-stone-900 text-white' : 'text-stone-600 hover:bg-stone-100' ?>">
                  <?= e($l) ?>
                  <span class="text-xs tabular-nums <?= $active ? 'text-stone-400' : 'text-stone-400' ?>"><?= (int) $count ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Type filter -->
          <?php if (!empty($types)): ?>
          <div class="border-t border-stone-100 pt-6">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 mb-3">Vehicle Type</p>
            <div class="flex flex-wrap gap-2">
              <a href="<?= e(build_url(['type' => 'all'])) ?>"
                 class="rounded-full border px-3 py-1 text-xs font-medium transition-colors <?= $typeFilter === 'all' ? 'border-stone-900 bg-stone-900 text-white' : 'border-stone-200 text-stone-600 hover:border-stone-400' ?>">
                All
              </a>
              <?php foreach ($types as $t): ?>
                <a href="<?= e(build_url(['type' => $t])) ?>"
                   class="rounded-full border px-3 py-1 text-xs font-medium transition-colors <?= $typeFilter === $t ? 'border-stone-900 bg-stone-900 text-white' : 'border-stone-200 text-stone-600 hover:border-stone-400' ?>">
                  <?= e($t) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($hasFilters): ?>
          <a href="<?= e(url('/user/cars.php')) ?>"
             class="flex items-center gap-1.5 text-xs text-stone-400 hover:text-stone-700 transition-colors">
            <i data-lucide="x" style="width:12px;height:12px"></i> ล้างตัวกรอง
          </a>
          <?php endif; ?>
        </div>
      </aside>

      <!-- ── RESULTS ── -->
      <div class="lg:col-span-9">
        <div class="mb-8 flex items-center justify-between">
          <p class="text-sm text-stone-500">
            แสดง <span class="font-semibold text-stone-900 tabular-nums"><?= count($filtered) ?></span>
            จาก <?= count($cars) ?> คัน
          </p>
          <?php if ($hasFilters): ?>
            <div class="flex items-center gap-2 rounded-full bg-stone-100 px-3 py-1">
              <i data-lucide="sliders-horizontal" class="text-stone-500" style="width:12px;height:12px"></i>
              <span class="text-xs text-stone-600">Filtered</span>
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($filtered)): ?>
          <div class="reveal rounded-xl border border-stone-200 bg-stone-50 py-20 text-center">
            <p class="display-serif text-2xl italic text-stone-400">No vehicles found.</p>
            <p class="mt-2 text-sm text-stone-500">ลองล้างตัวกรองแล้วค้นหาใหม่</p>
            <a href="<?= e(url('/user/cars.php')) ?>" class="btn-secondary mt-5 px-5 py-2">ล้างตัวกรอง</a>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3" data-stagger="70">
            <?php foreach ($filtered as $car): ?>
              <div class="reveal-scale">
                <?php include __DIR__ . '/_car_card.php'; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </form>
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
