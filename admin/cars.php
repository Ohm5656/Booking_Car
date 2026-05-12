<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Handle delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['_action'] ?? '') === 'delete') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM cars WHERE id = ?');
            $stmt->execute([$id]);
            flash('success', 'ลบรถแล้ว');
        } catch (Throwable $e) {
            flash('error', 'ลบรถไม่สำเร็จ — รถนี้ยังมีประวัติการจองอยู่');
        }
    }
    header('Location: ' . url('/admin/cars.php'));
    exit;
}

$pageTitle   = 'Admin · Vehicles';
$currentPage = 'cars';
require __DIR__ . '/_layout_start.php';

$cars = $pdo->query('SELECT * FROM cars ORDER BY created_at DESC')->fetchAll();
?>

<div class="space-y-10">
  <header class="border-b border-stone-200 pb-8">
    <div class="flex flex-wrap items-end justify-between gap-6">
      <div>
        <p class="eyebrow">Fleet Management</p>
        <h1 class="display-serif mt-2.5 text-[42px] leading-[0.95] text-stone-900">
          Vehicles<span class="text-copper-500">.</span>
        </h1>
        <p class="mt-3 max-w-xl text-sm text-stone-500">เพิ่ม แก้ไข หรือนำรถออกจากระบบ</p>
      </div>
      <a href="<?= e(url('/admin/car-create.php')) ?>" class="btn-primary">
        <i data-lucide="plus" class="mr-2" style="width:16px;height:16px"></i>
        เพิ่มรถ
      </a>
    </div>
  </header>

  <section class="surface p-2">
    <?php if (empty($cars)): ?>
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
              <?php foreach (['ชื่อรถ','ป้ายทะเบียน','ประเภท','ที่นั่ง','สถานะ','จัดการ'] as $h): ?>
                <th class="whitespace-nowrap px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.14em] text-stone-500"><?= e($h) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100 bg-white">
            <?php foreach ($cars as $c): ?>
              <tr class="transition-colors duration-150 hover:bg-stone-50">
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($c['name']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm"><span class="font-mono text-stone-600"><?= e($c['license_plate']) ?></span></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($c['type']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><span class="tabular-nums"><?= (int) $c['seats'] ?></span></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm"><?= status_badge($c['status']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm">
                  <div class="flex items-center gap-1">
                    <a href="<?= e(url('/admin/car-edit.php?id=' . (int) $c['id'])) ?>"
                       class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-100 hover:text-stone-900">
                      <i data-lucide="pencil" style="width:13px;height:13px"></i>
                      แก้ไข
                    </a>
                    <form method="post" class="inline" data-confirm="ลบรถคันนี้? การลบไม่สามารถย้อนกลับได้">
                      <?= csrf_field() ?>
                      <input type="hidden" name="_action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                      <button type="submit"
                              class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700">
                        <i data-lucide="trash-2" style="width:13px;height:13px"></i>
                        ลบ
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
