<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Aggregations
$bookingsByStatus = $pdo->query("
    SELECT status, COUNT(*) AS c FROM bookings GROUP BY status
")->fetchAll();
$carsByType = $pdo->query("
    SELECT type, COUNT(*) AS c FROM cars GROUP BY type
")->fetchAll();

$STATUS_LABELS = [
    'pending'   => 'รอดำเนินการ',
    'approved'  => 'อนุมัติแล้ว',
    'rejected'  => 'ไม่อนุมัติ',
    'completed' => 'คืนรถแล้ว',
];
$bookingsData = array_map(function ($row) use ($STATUS_LABELS) {
    return [
        'name'  => $STATUS_LABELS[$row['status']] ?? $row['status'],
        'value' => (int) $row['c'],
    ];
}, $bookingsByStatus);

$typesData = array_map(function ($row) {
    return ['name' => $row['type'], 'value' => (int) $row['c']];
}, $carsByType);

$totalBookings = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$totalCars     = (int) $pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn();

$pageTitle   = 'Admin · Reports';
$currentPage = 'reports';

// Inject Chart.js into <head>
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>';
require __DIR__ . '/_layout_start.php';
?>

<div class="space-y-10">
  <header class="border-b border-stone-200 pb-8">
    <p class="eyebrow">Analytics</p>
    <h1 class="display-serif mt-2.5 text-[42px] leading-[0.95] text-stone-900">
      Reports<span class="text-copper-500">.</span>
    </h1>
    <p class="mt-3 max-w-xl text-sm text-stone-500">อัตราการใช้งานและกิจกรรมของฟลีท</p>
  </header>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <section class="surface">
      <header class="border-b border-stone-200 px-5 py-4">
        <div class="flex items-baseline gap-3">
          <span class="section-number text-xl text-stone-300">01</span>
          <h2 class="display-serif text-[18px] leading-none text-stone-900">การจองแยกตามสถานะ</h2>
        </div>
        <p class="mt-1.5 pl-9 text-xs text-stone-500">สัดส่วนคำขอในแต่ละสถานะของระบบ</p>
      </header>
      <div class="p-4">
        <div class="h-72 w-full"><canvas id="bookingsChart"></canvas></div>
      </div>
    </section>

    <section class="surface">
      <header class="border-b border-stone-200 px-5 py-4">
        <div class="flex items-baseline gap-3">
          <span class="section-number text-xl text-stone-300">02</span>
          <h2 class="display-serif text-[18px] leading-none text-stone-900">รถแยกตามประเภท</h2>
        </div>
        <p class="mt-1.5 pl-9 text-xs text-stone-500">องค์ประกอบของฟลีทที่มีอยู่ปัจจุบัน</p>
      </header>
      <div class="p-4">
        <div class="h-72 w-full"><canvas id="carsChart"></canvas></div>
      </div>
    </section>
  </div>

  <section class="surface">
    <header class="border-b border-stone-200 px-5 py-4">
      <div class="flex items-baseline gap-3">
        <span class="section-number text-xl text-stone-300">03</span>
        <h2 class="display-serif text-[18px] leading-none text-stone-900">สรุปภาพรวม</h2>
      </div>
    </header>
    <div class="grid grid-cols-1 divide-y divide-stone-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
      <div class="p-6">
        <p class="eyebrow">การจองทั้งหมด</p>
        <p class="display-serif mt-2.5 text-[40px] leading-none text-stone-900 tabular-nums"><?= (int) $totalBookings ?></p>
      </div>
      <div class="p-6">
        <p class="eyebrow">ขนาดฟลีท</p>
        <p class="display-serif mt-2.5 text-[40px] leading-none text-stone-900 tabular-nums"><?= (int) $totalCars ?></p>
      </div>
    </div>
  </section>
</div>

<script>
(function () {
  var bookings = <?= json_encode($bookingsData, JSON_UNESCAPED_UNICODE) ?>;
  var cars     = <?= json_encode($typesData,    JSON_UNESCAPED_UNICODE) ?>;
  var PALETTE  = ['#1e40af', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#6366f1'];

  // Bar chart (booking statuses) — navy bars, matches Recharts version
  new Chart(document.getElementById('bookingsChart'), {
    type: 'bar',
    data: {
      labels: bookings.map(function (r) { return r.name; }),
      datasets: [{
        label: 'Count',
        data: bookings.map(function (r) { return r.value; }),
        backgroundColor: '#1e40af',
        borderRadius: 3,
        maxBarThickness: 48,
      }],
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#ffffff', borderColor: '#e2e8f0', borderWidth: 1,
          titleColor: '#0f172a', bodyColor: '#475569', padding: 8, cornerRadius: 6,
        },
      },
      scales: {
        x: {
          ticks: { color: '#64748b', font: { size: 12 } },
          grid: { display: false },
          border: { color: '#cbd5e1' },
        },
        y: {
          ticks: { color: '#64748b', font: { size: 12 } },
          border: { display: false },
          grid: { color: '#e2e8f0', drawBorder: false },
          beginAtZero: true,
          precision: 0,
        },
      },
    },
  });

  // Donut chart (cars by type)
  new Chart(document.getElementById('carsChart'), {
    type: 'doughnut',
    data: {
      labels: cars.map(function (r) { return r.name; }),
      datasets: [{
        data: cars.map(function (r) { return r.value; }),
        backgroundColor: cars.map(function (_, i) { return PALETTE[i % PALETTE.length]; }),
        borderColor: '#ffffff',
        borderWidth: 2,
      }],
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      cutout: '56%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#475569', font: { size: 12 }, boxWidth: 8, boxHeight: 8, padding: 14, usePointStyle: true },
        },
        tooltip: {
          backgroundColor: '#ffffff', borderColor: '#e2e8f0', borderWidth: 1,
          titleColor: '#0f172a', bodyColor: '#475569', padding: 8, cornerRadius: 6,
        },
      },
    },
  });
})();
</script>

<?php require __DIR__ . '/_layout_end.php'; ?>
