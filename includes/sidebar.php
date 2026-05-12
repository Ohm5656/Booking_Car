<?php
/**
 * Admin sidebar — mirrors src/components/layout/Sidebar.tsx.
 *
 * Variables expected:
 *   $user        — current_user() result
 *   $currentPage — one of: 'overview' | 'cars' | 'requests' | 'history' | 'reports'
 */
$user        = $user        ?? current_user();
$currentPage = $currentPage ?? '';

$navigation = [
    ['name' => 'Overview',  'href' => url('/admin/dashboard.php'), 'icon' => 'layout-dashboard', 'key' => 'overview'],
    ['name' => 'Vehicles',  'href' => url('/admin/cars.php'),      'icon' => 'car',              'key' => 'cars'],
    ['name' => 'Requests',  'href' => url('/admin/requests.php'),  'icon' => 'inbox',            'key' => 'requests'],
    ['name' => 'History',   'href' => url('/admin/bookings.php'),  'icon' => 'file-clock',       'key' => 'history'],
    ['name' => 'Reports',   'href' => url('/admin/reports.php'),   'icon' => 'bar-chart-3',      'key' => 'reports'],
];
?>
<aside class="flex w-60 flex-col border-r border-stone-800 bg-stone-950 min-h-screen">
  <!-- Logo -->
  <div class="flex items-center gap-2.5 border-b border-stone-800 px-5 py-4">
    <span class="flex h-7 w-7 items-center justify-center rounded bg-white text-stone-950">
      <i data-lucide="car-front" style="width:15px;height:15px"></i>
    </span>
    <div>
      <span class="display-serif text-[17px] leading-none text-white">AutoBook</span>
      <span class="ml-1.5 rounded px-1 py-0.5 text-[9px] font-semibold uppercase tracking-wider bg-copper-600 text-white">Admin</span>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
    <p class="mb-3 px-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-stone-600">Management</p>
    <?php foreach ($navigation as $item):
      $active = ($currentPage === $item['key']);
      $linkCls = $active
        ? 'bg-stone-800 text-white'
        : 'text-stone-400 hover:bg-stone-900 hover:text-stone-200';
      $iconCls = $active ? 'text-copper-400' : 'text-stone-600 group-hover:text-stone-400';
    ?>
      <a href="<?= e($item['href']) ?>"
         class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors <?= $linkCls ?>">
        <i data-lucide="<?= e($item['icon']) ?>" class="flex-shrink-0 <?= $iconCls ?>" style="width:16px;height:16px"></i>
        <?= e($item['name']) ?>
        <?php if ($active): ?>
          <span class="ml-auto h-1.5 w-1.5 rounded-full bg-copper-500"></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Footer -->
  <div class="border-t border-stone-800 p-4">
    <div class="mb-3 flex items-center gap-3">
      <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-stone-800 text-stone-400">
        <i data-lucide="shield-check" style="width:15px;height:15px"></i>
      </span>
      <div class="min-w-0">
        <p class="truncate text-sm font-medium text-stone-200"><?= e($user['name'] ?? 'Administrator') ?></p>
        <p class="truncate text-[11px] text-stone-500"><?= e($user['email'] ?? '') ?></p>
      </div>
    </div>
    <form method="post" action="<?= e(url('/auth/logout.php')) ?>">
      <?= csrf_field() ?>
      <button type="submit"
              class="flex w-full items-center justify-center gap-2 rounded-md px-3 py-2 text-xs font-medium text-stone-500 transition-colors hover:bg-stone-900 hover:text-stone-300">
        <i data-lucide="log-out" style="width:13px;height:13px"></i>
        Sign Out
      </button>
    </form>
  </div>
</aside>
