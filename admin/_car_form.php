<?php
/**
 * Shared add/edit car form — car-create.php and car-edit.php.
 * Expected: $form (assoc) + $editingCar (truthy for edit) + $errors (array)
 */
$editingCar = $editingCar ?? null;
$errors     = $errors     ?? [];
$form       = $form       ?? [
    'name' => '', 'licensePlate' => '', 'type' => '',
    'seats' => '', 'description' => '', 'image' => '',
];
?>

<div class="mx-auto max-w-2xl space-y-10">

  <!-- Page header -->
  <header class="border-b border-stone-200 pb-8">
    <p class="eyebrow">Fleet Management</p>
    <h1 class="display-serif mt-2.5 text-[42px] leading-[0.95] text-stone-900">
      <?= $editingCar ? 'แก้ไขข้อมูลรถ' : 'เพิ่มรถใหม่' ?><span class="text-copper-500">.</span>
    </h1>
    <p class="mt-3 text-sm text-stone-500">
      <?= $editingCar ? 'แก้ไขรายละเอียดของรถในระบบ' : 'กรอกรายละเอียดของรถใหม่ที่จะเพิ่มเข้าระบบ' ?>
    </p>
  </header>

  <?php if (!empty($errors)): ?>
    <div class="flex items-start gap-3 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
      <i data-lucide="alert-circle" class="mt-0.5 flex-shrink-0" style="width:15px;height:15px"></i>
      <?= e($errors[0]) ?>
    </div>
  <?php endif; ?>

  <section class="surface p-6 sm:p-8">
    <form method="post" enctype="multipart/form-data" class="space-y-5">
      <?= csrf_field() ?>
      <?php if ($editingCar): ?>
        <input type="hidden" name="id" value="<?= (int) $editingCar['id'] ?>">
      <?php endif; ?>

      <!-- ── IMAGE UPLOAD ──────────────────────────────── -->
      <div>
        <label class="block text-sm font-medium text-stone-700 mb-2">รูปภาพรถ</label>

        <label for="img-input" id="img-drop-zone"
               class="group relative block w-full cursor-pointer overflow-hidden rounded-xl border-2 border-dashed border-stone-200 bg-stone-50 transition-colors hover:border-stone-400 hover:bg-stone-100">

          <!-- Current / preview image -->
          <?php
          $currentImg = null;
          if ($editingCar && !empty($editingCar['image'])) {
              $currentImg = url('/assets/images/cars/' . $editingCar['image']);
          }
          ?>
          <img id="img-preview"
               src="<?= $currentImg ? e($currentImg) : '' ?>"
               alt="preview"
               class="<?= $currentImg ? '' : 'hidden' ?> aspect-[16/10] w-full object-cover">

          <!-- Placeholder (shown when no image) -->
          <div id="img-placeholder" class="<?= $currentImg ? 'hidden' : '' ?> flex flex-col items-center justify-center py-14 text-center">
            <i data-lucide="image-plus" class="mb-3 text-stone-300 transition-colors group-hover:text-stone-400" style="width:44px;height:44px;stroke-width:1.25"></i>
            <p class="text-sm font-medium text-stone-600">คลิกเพื่ออัปโหลดรูปภาพ</p>
            <p class="mt-1 text-xs text-stone-400">JPG · PNG · WebP · ไม่เกิน 5 MB</p>
          </div>

          <!-- Overlay change hint (shown when image loaded) -->
          <div id="img-change-hint"
               class="<?= $currentImg ? '' : 'hidden' ?> absolute inset-0 flex flex-col items-center justify-center rounded-xl bg-stone-950/40 opacity-0 transition-opacity group-hover:opacity-100">
            <i data-lucide="camera" class="mb-1 text-white" style="width:28px;height:28px;stroke-width:1.5"></i>
            <p class="text-xs font-medium text-white">เปลี่ยนรูปภาพ</p>
          </div>

          <input type="file" id="img-input" name="image"
                 accept="image/jpeg,image/png,image/webp"
                 class="absolute inset-0 cursor-pointer opacity-0">
        </label>

        <?php if ($editingCar && !empty($editingCar['image'])): ?>
          <p class="mt-1.5 text-xs text-stone-400">
            <i data-lucide="info" class="inline align-middle" style="width:11px;height:11px"></i>
            อัปโหลดไฟล์ใหม่เพื่อเปลี่ยนรูป หรือปล่อยว่างไว้เพื่อคงรูปเดิม
          </p>
        <?php endif; ?>
      </div>

      <!-- ── CAR NAME ──────────────────────────────────── -->
      <div>
        <label class="block text-sm font-medium text-stone-700 mb-1.5">ชื่อรถ</label>
        <input type="text" name="name" required
               value="<?= e($form['name']) ?>" class="input-modern" placeholder="เช่น Toyota Camry">
      </div>

      <!-- ── LICENSE PLATE ─────────────────────────────── -->
      <div>
        <label class="block text-sm font-medium text-stone-700 mb-1.5">ป้ายทะเบียน</label>
        <input type="text" name="licensePlate" required
               value="<?= e($form['licensePlate']) ?>" class="input-modern font-mono" placeholder="เช่น กข 1234">
      </div>

      <!-- ── TYPE + SEATS ──────────────────────────────── -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-stone-700 mb-1.5">ประเภท</label>
          <select name="type" required class="input-modern">
            <option value="">เลือก…</option>
            <?php foreach (['Sedan','SUV','Pickup','Van','Minivan'] as $t): ?>
              <option value="<?= $t ?>" <?= $form['type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-stone-700 mb-1.5">จำนวนที่นั่ง</label>
          <input type="number" name="seats" required min="2" max="50"
                 value="<?= e($form['seats']) ?>" class="input-modern">
        </div>
      </div>

      <!-- ── DESCRIPTION ───────────────────────────────── -->
      <div>
        <label class="block text-sm font-medium text-stone-700 mb-1.5">รายละเอียด</label>
        <textarea name="description" rows="3" class="input-modern resize-none"
                  placeholder="ระบุรายละเอียดเพิ่มเติม (ไม่บังคับ)"><?= e($form['description']) ?></textarea>
      </div>

      <!-- ── ACTIONS ───────────────────────────────────── -->
      <div class="flex justify-end gap-3 border-t border-stone-200 pt-5">
        <a href="<?= e(url('/admin/cars.php')) ?>" class="btn-secondary">ยกเลิก</a>
        <button type="submit" class="btn-primary gap-2">
          <i data-lucide="<?= $editingCar ? 'save' : 'plus' ?>" style="width:14px;height:14px"></i>
          <?= $editingCar ? 'บันทึกการแก้ไข' : 'เพิ่มรถใหม่' ?>
        </button>
      </div>
    </form>
  </section>
</div>

<script>
(function () {
  var input = document.getElementById('img-input');
  var preview = document.getElementById('img-preview');
  var placeholder = document.getElementById('img-placeholder');
  var hint = document.getElementById('img-change-hint');
  if (!input) return;

  input.addEventListener('change', function () {
    var file = input.files && input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.classList.remove('hidden');
      placeholder.classList.add('hidden');
      if (hint) hint.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
  });
})();
</script>
