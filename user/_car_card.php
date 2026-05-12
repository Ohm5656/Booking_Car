<?php
/**
 * Car card partial — mirrors src/components/cars/CarCard.tsx.
 *
 * Expected in scope: $car (row from `cars` table).
 */
$car = $car ?? null;
if (!$car) return;
$isAvailable = ($car['status'] === 'available');
$imageUrl    = car_image($car['type'], $car['image'] ?? null);
?>
<article class="group overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm card-hover">
  <!-- Image -->
  <div class="shimmer-wrap relative aspect-[16/10] overflow-hidden bg-stone-100">
    <img src="<?= e($imageUrl) ?>" alt="<?= e($car['name']) ?>"
         class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 cubic-bezier(0.16,1,0.3,1) group-hover:scale-[1.06]"
         loading="lazy">
    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
    <div class="absolute left-3 top-3">
      <span class="rounded-md bg-white/95 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-stone-700 shadow-sm">
        <?= e($car['type']) ?>
      </span>
    </div>
    <div class="absolute right-3 top-3">
      <?= status_badge($car['status']) ?>
    </div>
  </div>

  <!-- Body -->
  <div class="p-5">
    <div class="flex items-start justify-between gap-2">
      <div>
        <h3 class="font-semibold text-stone-900 leading-tight"><?= e($car['name']) ?></h3>
        <p class="mt-0.5 font-mono text-xs text-stone-400"><?= e($car['license_plate']) ?></p>
      </div>
    </div>

    <div class="mt-3 flex items-center gap-4 text-xs text-stone-500">
      <span class="flex items-center gap-1">
        <i data-lucide="users" class="text-stone-400" style="width:12px;height:12px"></i>
        <?= (int) $car['seats'] ?> ที่นั่ง
      </span>
      <span class="flex items-center gap-1">
        <i data-lucide="tag" class="text-stone-400" style="width:12px;height:12px"></i>
        <?= e($car['type']) ?>
      </span>
    </div>

    <?php if (!empty($car['description'])): ?>
      <p class="mt-3 line-clamp-2 text-xs leading-relaxed text-stone-500">
        <?= e($car['description']) ?>
      </p>
    <?php endif; ?>

    <div class="mt-4 border-t border-stone-100 pt-4">
      <?php if ($isAvailable): ?>
        <a href="<?= e(url('/user/booking-create.php?car_id=' . (int) $car['id'])) ?>"
           class="group/cta flex w-full items-center justify-between rounded-lg bg-stone-900 px-4 py-2.5 text-sm font-medium text-white transition-all duration-300 hover:bg-stone-800 hover:shadow-[0_4px_16px_-4px_rgba(15,23,42,0.4)] active:scale-[0.98]">
          <span>จองรถคันนี้</span>
          <i data-lucide="arrow-up-right" class="transition-transform duration-300 group-hover/cta:-translate-y-0.5 group-hover/cta:translate-x-0.5" style="width:16px;height:16px"></i>
        </a>
      <?php else: ?>
        <div class="flex w-full items-center justify-center rounded-lg border border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-400">
          ไม่ว่างในขณะนี้
        </div>
      <?php endif; ?>
    </div>
  </div>
</article>
