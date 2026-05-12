<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Complete a booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['_action'] ?? '') === 'complete') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $b = $stmt->fetch();
        if ($b && $b['status'] === 'approved') {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$id]);
                $pdo->prepare("UPDATE cars SET status='available' WHERE id=?")->execute([$b['car_id']]);
                $pdo->commit();
                flash('success', 'ทำเครื่องหมายว่าคืนรถแล้ว');
            } catch (Throwable $e) {
                $pdo->rollBack();
                flash('error', 'อัปเดตสถานะไม่สำเร็จ');
            }
        }
    }
    $back = url('/admin/bookings.php');
    if (!empty($_POST['filter'])) $back .= '?status=' . urlencode($_POST['filter']);
    header('Location: ' . $back);
    exit;
}

$filter = (string) ($_GET['status'] ?? '');
$valid  = ['pending', 'approved', 'rejected', 'completed'];

$sql = "SELECT b.*, u.name AS user_name, c.name AS car_name, c.license_plate
        FROM bookings b
        JOIN users u ON u.id = b.user_id
        JOIN cars  c ON c.id = b.car_id";
$params = [];
if (in_array($filter, $valid, true)) {
    $sql .= ' WHERE b.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY b.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle   = 'Admin · History';
$currentPage = 'history';
require __DIR__ . '/_layout_start.php';
?>

<div class="space-y-10">
  <header class="border-b border-stone-200 pb-8">
    <div class="flex flex-wrap items-end justify-between gap-6">
      <div>
        <p class="eyebrow">Records</p>
        <h1 class="display-serif mt-2.5 text-[42px] leading-[0.95] text-stone-900">
          History<span class="text-copper-500">.</span>
        </h1>
        <p class="mt-3 max-w-xl text-sm text-stone-500">ประวัติการจองทั้งหมดของระบบ</p>
      </div>

      <form method="get" class="w-full sm:w-48">
        <select name="status" class="input-modern w-full py-2" onchange="this.form.submit()">
          <option value="">สถานะทั้งหมด</option>
          <option value="pending"   <?= $filter === 'pending'   ? 'selected' : '' ?>>รอดำเนินการ</option>
          <option value="approved"  <?= $filter === 'approved'  ? 'selected' : '' ?>>อนุมัติแล้ว</option>
          <option value="rejected"  <?= $filter === 'rejected'  ? 'selected' : '' ?>>ไม่อนุมัติ</option>
          <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>คืนรถแล้ว</option>
        </select>
      </form>
    </div>
  </header>

  <section class="surface p-2">
    <?php if (empty($bookings)): ?>
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
              <?php foreach (['รหัส','ผู้ใช้งาน','รถ','ช่วงเวลา','สถานะ','จัดการ'] as $h): ?>
                <th class="whitespace-nowrap px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.14em] text-stone-500"><?= e($h) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100 bg-white">
            <?php foreach ($bookings as $b):
              $sd = thai_date($b['start_date']);
              $ed = thai_date($b['end_date']);
            ?>
              <tr class="transition-colors duration-150 hover:bg-stone-50">
                <td class="whitespace-nowrap px-6 py-4 text-sm">
                  <span class="font-mono text-[11px] text-stone-400">#<?= e(str_pad((string) $b['id'], 8, '0', STR_PAD_LEFT)) ?></span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($b['user_name']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700">
                  <?= e($b['car_name']) ?>
                  <span class="font-mono text-xs text-stone-400">(<?= e($b['license_plate']) ?>)</span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($sd['long']) ?> – <?= e($ed['long']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm"><?= status_badge($b['status']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm">
                  <?php if ($b['status'] === 'approved'): ?>
                    <form method="post" class="inline" data-confirm="ทำเครื่องหมายว่าคืนรถแล้ว? รถจะกลับมาว่างให้จองอีกครั้ง">
                      <?= csrf_field() ?>
                      <input type="hidden" name="_action" value="complete">
                      <input type="hidden" name="id"      value="<?= (int) $b['id'] ?>">
                      <input type="hidden" name="filter"  value="<?= e($filter) ?>">
                      <button type="submit"
                              class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700 hover:bg-emerald-100">
                        ทำเครื่องหมายว่าคืนรถแล้ว
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-stone-300">—</span>
                  <?php endif; ?>
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
