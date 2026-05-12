<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'ไม่พบรถที่ระบุ');
    header('Location: ' . url('/admin/cars.php'));
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$car = $stmt->fetch();
if (!$car) {
    flash('error', 'ไม่พบรถที่ระบุ');
    header('Location: ' . url('/admin/cars.php'));
    exit;
}

$errors = [];
$form = [
    'name'         => $car['name'],
    'licensePlate' => $car['license_plate'],
    'type'         => $car['type'],
    'seats'        => (string) $car['seats'],
    'description'  => $car['description'] ?? '',
    'image'        => $car['image'] ?? '',
];
$editingCar = $car;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['name','licensePlate','type','seats','description'] as $k) {
        $form[$k] = trim((string) ($_POST[$k] ?? $form[$k]));
    }

    if ($form['name'] === '' || $form['licensePlate'] === '' || $form['type'] === '' || $form['seats'] === '') {
        $errors[] = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    }

    // Handle image upload — optional, keep existing if none provided
    $newFilename = null;
    if (empty($errors) && !empty($_FILES['image']['tmp_name'])) {
        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            $errors[] = 'ไฟล์รูปต้องเป็น JPG, PNG หรือ WebP เท่านั้น';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'ขนาดไฟล์ต้องไม่เกิน 5 MB';
        } else {
            $ext       = $allowed[$mime];
            $uploadDir = __DIR__ . '/../assets/images/cars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = uniqid('car_', true) . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $errors[] = 'อัปโหลดรูปภาพไม่สำเร็จ';
            } else {
                $newFilename = $filename;
            }
        }
    }

    if (empty($errors)) {
        // Use new image if uploaded, else keep existing
        $imageToSave = $newFilename ?? ($car['image'] ?: null);

        try {
            $stmt = $pdo->prepare(
                'UPDATE cars SET name=?, license_plate=?, type=?, seats=?, description=?, image=? WHERE id=?'
            );
            $stmt->execute([
                $form['name'], $form['licensePlate'], $form['type'],
                (int) $form['seats'],
                $form['description'] ?: null,
                $imageToSave,
                $id,
            ]);

            // Delete old image file if replaced
            if ($newFilename && !empty($car['image'])) {
                $oldPath = __DIR__ . '/../assets/images/cars/' . $car['image'];
                if (file_exists($oldPath)) @unlink($oldPath);
            }

            flash('success', 'อัปเดตข้อมูลรถแล้ว');
            header('Location: ' . url('/admin/cars.php'));
            exit;
        } catch (PDOException $e) {
            if ($newFilename) @unlink(__DIR__ . '/../assets/images/cars/' . $newFilename);
            $errors[] = ($e->getCode() === '23000') ? 'ป้ายทะเบียนนี้มีในระบบแล้ว' : 'บันทึกข้อมูลรถไม่สำเร็จ';
        }
    }

    // Refresh editingCar image for form re-render
    $editingCar = array_merge($car, ['image' => $newFilename ?? $car['image']]);
}

$pageTitle   = 'Admin · Edit Vehicle';
$currentPage = 'cars';
require __DIR__ . '/_layout_start.php';
include __DIR__ . '/_car_form.php';
require __DIR__ . '/_layout_end.php';
