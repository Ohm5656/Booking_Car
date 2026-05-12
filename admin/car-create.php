<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$errors = [];
$form = [
    'name'         => '',
    'licensePlate' => '',
    'type'         => '',
    'seats'        => '',
    'description'  => '',
    'image'        => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['name','licensePlate','type','seats','description'] as $k) {
        $form[$k] = trim((string) ($_POST[$k] ?? ''));
    }

    if ($form['name'] === '' || $form['licensePlate'] === '' || $form['type'] === '' || $form['seats'] === '') {
        $errors[] = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    }

    // Handle image upload
    $uploadedFilename = null;
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
                $uploadedFilename = $filename;
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO cars (name, license_plate, type, seats, description, image, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $form['name'], $form['licensePlate'], $form['type'],
                (int) $form['seats'],
                $form['description'] ?: null,
                $uploadedFilename,
                'available',
            ]);
            flash('success', 'เพิ่มรถใหม่แล้ว');
            header('Location: ' . url('/admin/cars.php'));
            exit;
        } catch (PDOException $e) {
            if ($uploadedFilename) @unlink(__DIR__ . '/../assets/images/cars/' . $uploadedFilename);
            $errors[] = ($e->getCode() === '23000') ? 'ป้ายทะเบียนนี้มีในระบบแล้ว' : 'บันทึกข้อมูลรถไม่สำเร็จ';
        }
    }
}

$pageTitle   = 'Admin · Add Vehicle';
$currentPage = 'cars';
require __DIR__ . '/_layout_start.php';
include __DIR__ . '/_car_form.php';
require __DIR__ . '/_layout_end.php';
