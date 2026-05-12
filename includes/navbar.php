<?php
/**
 * User-side top navigation — mirrors src/components/layout/Navbar.tsx.
 *
 * Variables expected in scope:
 *   $user           — array from current_user()
 *   $currentPage    — one of: 'dashboard' | 'cars' | 'my-bookings'
 *   $transparentNav — bool, true on the dashboard hero (sets dark-on-image style)
 */
$user           = $user           ?? current_user();
$currentPage    = $currentPage    ?? '';
$transparentNav = $transparentNav ?? false;

$navLinks = [
    ['name' => 'Dashboard',   'href' => url('/user/dashboard.php'),    'icon' => 'layout-dashboard', 'key' => 'dashboard'],
    ['name' => 'Vehicles',    'href' => url('/user/cars.php'),         'icon' => 'car',              'key' => 'cars'],
    ['name' => 'My Bookings', 'href' => url('/user/my-bookings.php'),  'icon' => 'calendar-days',    'key' => 'my-bookings'],
];
?>
<nav
  id="site-nav"
  data-transparent-default="<?= $transparentNav ? '1' : '0' ?>"
  class="fixed top-0 z-50 w-full transition-all duration-300 <?= $transparentNav
        ? 'bg-transparent border-transparent py-4'
        : 'bg-white/95 border-b border-stone-200 backdrop-blur-md py-0 shadow-sm' ?>"
>
  <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-6 lg:px-10">
    <!-- Logo -->
    <a href="<?= e(url('/user/dashboard.php')) ?>" class="flex items-center gap-2.5 group">
      <span class="nav-logo-bg flex h-7 w-7 items-center justify-center rounded transition-colors <?= $transparentNav ? 'bg-white text-stone-900' : 'bg-stone-900 text-white' ?>">
        <i data-lucide="car-front" style="width:15px;height:15px"></i>
      </span>
      <span class="nav-logo-text display-serif text-[18px] leading-none transition-colors <?= $transparentNav ? 'text-white' : 'text-stone-900 group-hover:text-stone-700' ?>">
        AutoBook
      </span>
    </a>

    <!-- Nav Links -->
    <div class="hidden sm:flex items-center gap-1">
      <?php foreach ($navLinks as $link):
        $active = ($currentPage === $link['key']);
        $base   = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-sm transition-all';
        if ($active) {
          $cls = $base . ' nav-link-active ' . ($transparentNav ? 'bg-white/10 text-white font-medium' : 'bg-stone-100 text-stone-900 font-medium');
        } else {
          $cls = $base . ' nav-link-idle ' . ($transparentNav ? 'text-stone-300 hover:text-white hover:bg-white/10' : 'text-stone-500 hover:text-stone-900 hover:bg-stone-50');
        }
        $iconClass = $active
          ? ($transparentNav ? 'text-white' : 'text-copper-500')
          : 'text-stone-400';
      ?>
        <a href="<?= e($link['href']) ?>" class="<?= $cls ?>" data-nav-active="<?= $active ? '1' : '0' ?>">
          <i data-lucide="<?= e($link['icon']) ?>" class="<?= $iconClass ?>" style="width:14px;height:14px"></i>
          <?= e($link['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-4">
      <a href="<?= e(url('/user/cars.php')) ?>"
         class="nav-cta hidden sm:inline-flex items-center gap-1 rounded-full px-4 py-1.5 text-xs font-semibold transition-all <?= $transparentNav ? 'bg-white text-stone-900 hover:bg-stone-100' : 'bg-stone-900 text-white hover:bg-stone-800' ?>">
        Book Now
        <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
      </a>
      <div class="hidden sm:block text-right">
        <p class="nav-username text-xs font-semibold leading-tight transition-colors <?= $transparentNav ? 'text-white' : 'text-stone-700' ?>"><?= e($user['name'] ?? 'User') ?></p>
      </div>
      <form method="post" action="<?= e(url('/auth/logout.php')) ?>" class="inline">
        <?= csrf_field() ?>
        <button type="submit"
                class="nav-logout inline-flex items-center gap-1.5 text-xs transition-colors <?= $transparentNav ? 'text-stone-400 hover:text-white' : 'text-stone-400 hover:text-stone-700' ?>"
                title="ออกจากระบบ">
          <i data-lucide="log-out" style="width:14px;height:14px"></i>
        </button>
      </form>
    </div>
  </div>
</nav>
