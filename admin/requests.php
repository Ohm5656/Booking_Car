<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Handle approve / reject via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action    = (string) ($_POST['_action']  ?? '');
    $id        = (int)    ($_POST['id']       ?? 0);
    $adminNote = trim((string) ($_POST['adminNote'] ?? ''));

    if ($id > 0 && in_array($action, ['approve', 'reject'], true)) {
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $booking = $stmt->fetch();

        if ($booking) {
            if ($action === 'approve') {
                $pdo->beginTransaction();
                try {
                    $u1 = $pdo->prepare("UPDATE bookings SET status='approved', admin_note=? WHERE id=?");
                    $u1->execute([$adminNote ?: null, $id]);
                    $u2 = $pdo->prepare("UPDATE cars SET status='booked' WHERE id=?");
                    $u2->execute([$booking['car_id']]);
                    $pdo->commit();
                    flash('success', 'อนุมัติคำขอแล้ว');
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    flash('error', 'ดำเนินการคำขอไม่สำเร็จ');
                }
            } else {
                $stmt = $pdo->prepare("UPDATE bookings SET status='rejected', admin_note=? WHERE id=?");
                $stmt->execute([$adminNote ?: null, $id]);
                flash('success', 'ปฏิเสธคำขอแล้ว');
            }
        }
    }
    header('Location: ' . url('/admin/requests.php'));
    exit;
}

$pageTitle   = 'Admin · Requests';
$currentPage = 'requests';
require __DIR__ . '/_layout_start.php';

$requests = $pdo->query("
    SELECT b.*, u.name AS user_name, u.email AS user_email,
           c.name AS car_name, c.license_plate
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN cars  c ON c.id = b.car_id
    WHERE b.status = 'pending'
    ORDER BY b.created_at DESC
")->fetchAll();
?>

<div class="space-y-10">
  <header class="border-b border-stone-200 pb-8">
    <p class="eyebrow">Approvals Queue</p>
    <h1 class="display-serif mt-2.5 text-[42px] leading-[0.95] text-stone-900">
      Pending requests<span class="text-copper-500">.</span>
    </h1>
    <p class="mt-3 max-w-xl text-sm text-stone-500">ตรวจสอบ อนุมัติ หรือปฏิเสธคำขอจองจากผู้ใช้งาน</p>
  </header>

  <section class="surface p-2">
    <?php if (empty($requests)): ?>
      <div class="flex h-64 flex-col items-center justify-center text-center">
        <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 ring-1 ring-slate-200">
          <i data-lucide="folder-search" style="width:26px;height:26px;stroke-width:1.5"></i>
        </div>
        <p class="text-sm font-medium text-slate-600">ไม่มีคำขอที่รออนุมัติ</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto rounded-md border border-stone-200">
        <table class="min-w-full divide-y divide-stone-200">
          <thead class="bg-stone-50">
            <tr>
              <?php foreach (['ผู้ใช้งาน','รถ','ปลายทาง','ช่วงเวลา','เหตุผล','จัดการ'] as $h): ?>
                <th class="whitespace-nowrap px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.14em] text-stone-500"><?= e($h) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100 bg-white">
            <?php foreach ($requests as $r):
              $sd = thai_date($r['start_date']);
              $ed = thai_date($r['end_date']);
              $reason = mb_strlen($r['reason']) > 20 ? mb_substr($r['reason'], 0, 20) . '…' : $r['reason'];
            ?>
              <tr class="transition-colors duration-150 hover:bg-stone-50">
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($r['user_name']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($r['car_name']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($r['destination']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($sd['long']) ?> – <?= e($ed['long']) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-stone-700"><?= e($reason) ?></td>
                <td class="whitespace-nowrap px-6 py-4 text-sm">
                  <button type="button"
                          class="rounded-md border border-stone-300 bg-white px-2.5 py-1 text-[11px] font-medium text-stone-700 hover:bg-stone-50"
                          data-modal-open="reviewModal-<?= (int) $r['id'] ?>">
                    ตรวจสอบ
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- Per-request modals -->
<?php foreach ($requests as $r):
  $sd = thai_date($r['start_date']);
  $ed = thai_date($r['end_date']);
?>
  <div id="reviewModal-<?= (int) $r['id'] ?>" class="modal-backdrop" hidden>
    <div class="modal-panel">
      <div class="flex items-center justify-between border-b border-stone-100 px-6 py-4">
        <h2 class="display-serif text-base font-semibold text-stone-900">ตรวจสอบคำขอจอง</h2>
        <button type="button" class="rounded-md p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-600 transition-colors" data-modal-close aria-label="Close">
          <i data-lucide="x" style="width:18px;height:18px"></i>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" class="space-y-5">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
          <dl class="surface-muted divide-y divide-stone-200 text-sm">
            <?php
            $rows = [
              ['ผู้ใช้งาน', $r['user_name'] . ' — ' . $r['user_email']],
              ['เบอร์โทร',  $r['phone']],
              ['รถ',        $r['car_name'] . ' (' . $r['license_plate'] . ')'],
              ['ช่วงเวลา',  $sd['long'] . ' – ' . $ed['long']],
              ['ปลายทาง',   $r['destination']],
            ];
            foreach ($rows as [$k, $v]): ?>
              <div class="flex justify-between gap-4 px-4 py-2.5">
                <dt class="text-stone-500"><?= e($k) ?></dt>
                <dd class="text-right font-medium text-stone-900"><?= e($v) ?></dd>
              </div>
            <?php endforeach; ?>
            <div class="px-4 py-3">
              <dt class="text-stone-500 mb-1.5">เหตุผลการใช้รถ</dt>
              <dd class="rounded-md bg-white border border-stone-200 p-3 text-stone-700 leading-relaxed"><?= e($r['reason']) ?></dd>
            </div>
          </dl>

          <div>
            <label class="block text-sm font-medium text-stone-700 mb-1.5">หมายเหตุจากผู้ดูแล (ไม่บังคับ)</label>
            <textarea name="adminNote" rows="2" class="input-modern resize-none" placeholder="ระบุเหตุผลของการตัดสินใจ…"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-stone-200">
            <button type="button" class="btn-secondary" data-modal-close>ยกเลิก</button>
            <button type="submit" name="_action" value="reject"  class="btn-danger">ปฏิเสธ</button>
            <button type="submit" name="_action" value="approve" class="btn-success">อนุมัติ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/_layout_end.php'; ?>
