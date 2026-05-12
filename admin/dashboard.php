<?php
$pageTitle   = 'Admin · Overview';
$currentPage = 'overview';
require __DIR__ . '/_layout_start.php';

// Stats
$stats = [
    'totalCars'       => (int) $pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn(),
    'availableCars'   => (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE status='available'")->fetchColumn(),
    'bookedCars'      => (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE status='booked'")->fetchColumn(),
    'pendingRequests' => (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn(),
    'totalBookings'   => (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
];

// Pending requests (latest 5)
$pending = $pdo->query("
    SELECT b.*, u.name AS user_name, c.name AS car_name
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN cars  c ON c.id = b.car_id
    WHERE b.status='pending'
    ORDER BY b.created_at DESC
    LIMIT 5
")->fetchAll();

$today = thai_date(date('Y-m-d'));

/** Renders one StatsCard (mirrors StatsCard.tsx) */
function stats_card($title, $value, $icon, $tone) {
    $TONE = [
        'blue'    => ['icon' => 'bg-blue-50 text-blue-700 border-blue-200',         'val' => 'text-blue-700'],
        'emerald' => ['icon' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'val' => 'text-emerald-700'],
        'rose'    => ['icon' => 'bg-rose-50 text-rose-700 border-rose-200',         'val' => 'text-rose-700'],
        'amber'   => ['icon' => 'bg-amber-50 text-amber-700 border-amber-200',      'val' => 'text-amber-700'],
        'indigo'  => ['icon' => 'bg-indigo-50 text-indigo-700 border-indigo-200',   'val' => 'text-indigo-700'],
        'slate'   => ['icon' => 'bg-slate-100 text-slate-700 border-slate-200',     'val' => 'text-slate-700'],
    ];
    $t = $TONE[$tone] ?? $TONE['slate'];
    ?>
    <div class="surface flex items-center justify-between p-5 transition-shadow hover:shadow-md card-hover">
      <div>
        <p class="eyebrow mb-2"><?= e($title) ?></p>
        <p class="display-serif text-4xl leading-none <?= $t['val'] ?>"><?= e((string) $value) ?></p>
      </div>
      <div class="flex h-12 w-12 items-center justify-center rounded-lg border <?= $t['icon'] ?>">
        <i data-lucide="<?= e($icon) ?>" style="width:22px;height:22px"></i>
      </div>
    </div>
    <?php
}
?>

<div class="space-y-12">
  <!-- Page header -->
  <header class="border-b border-stone-200 pb-8">
    <div class="flex flex-wrap items-end justify-between gap-6">
      <div>
        <p class="eyebrow">Admin Console</p>
        <h1 class="display-serif mt-2.5 text-[42px] leading-[0.95] text-stone-900">
          Overview<span class="text-copper-500">.</span>
        </h1>
        <p class="mt-3 max-w-xl text-sm text-stone-500">ภาพรวมระบบและคำขอจองที่รอดำเนินการ</p>
      </div>
      <div class="text-right">
        <p class="eyebrow">Today</p>
        <p class="display-serif mt-1.5 text-xl text-stone-900"><?= e($today['long']) ?></p>
      </div>
    </div>
  </header>

  <!-- Stats -->
  <section>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
      <?php stats_card('รถทั้งหมด',      $stats['totalCars'],       'car-front',    'blue');    ?>
      <?php stats_card('พร้อมใช้งาน',    $stats['availableCars'],   'circle-check', 'emerald'); ?>
      <?php stats_card('ไม่ว่าง',          $stats['bookedCars'],      'lock',         'rose');    ?>
      <?php stats_card('รอดำเนินการ',    $stats['pendingRequests'], 'clock',        'amber');   ?>
      <?php stats_card('การจองทั้งหมด', $stats['totalBookings'],   'clipboard-list', 'indigo'); ?>
    </div>
  </section>

  <!-- Pending Requests Table -->
  <section class="surface">
    <header class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
      <div class="flex items-baseline gap-3">
        <span class="section-number text-2xl text-stone-300">01</span>
        <h2 class="display-serif text-[20px] leading-none text-stone-900">คำขอที่รออนุมัติ</h2>
      </div>
      <a href="<?= e(url('/admin/requests.php')) ?>" class="group inline-flex items-center gap-1 text-xs font-medium text-stone-900">
        ตรวจสอบทั้งหมด
        <i data-lucide="arrow-up-right" class="transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" style="width:12px;height:12px"></i>
      </a>
    </header>
    <div class="p-2">
      <?php if (empty($pending)): ?>
        <div class="flex h-64 flex-col items-center justify-center text-center">
          <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 ring-1 ring-slate-200">
            <i data-lucide="folder-search" style="width:26px;height:26px;stroke-width:1.5"></i>
          </div>
          <p class="text-sm font-medium text-slate-600">ไม่มีข้อมูลที่จะแสดง</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto rounded-md border border-stone-200">
          <table class="min-w-full divide-y divide-stone-200">
            <thead class="bg-stone-50">
              <tr>
                <?php foreach (['ผู้ใช้งาน','รถ','ปลายทาง','วันที่เริ่ม','สถานะ'] as $h): ?>
                  <th class="whitespace-nowrap px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.14em] text-stone-500"><?= e($h) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 bg-white">
              <?php foreach ($pending as $b):
                $sd = thai_date($b['start_date']);
              ?>
                <tr class="transition-colors duration-150 hover:bg-stone-50">
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($b['user_name']) ?></td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($b['car_name']) ?></td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($b['destination']) ?></td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($sd['long']) ?></td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm"><?= status_badge($b['status']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
