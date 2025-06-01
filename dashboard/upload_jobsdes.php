<?php
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mitra') {
    header("Location: ../login/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['mahasiswa_id']) || empty($_POST['mahasiswa_id'])) {
        die("Mahasiswa belum dipilih.");
    }
    $mahasiswa_id = $_POST['mahasiswa_id'];

    if (!isset($_FILES['jobsdesc'])) {
        die("File tidak ditemukan.");
    }

    $file = $_FILES['jobsdesc'];

    // Cek error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload file gagal dengan kode error: " . $file['error']);
    }

    // Validasi tipe file
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        die("Jenis file tidak diperbolehkan.");
    }

    // Direktori upload
    $upload_dir = '../uploads/jobsdesc/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Buat nama file unik supaya tidak tertimpa
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('jobsdesc_') . '.' . $ext;

    $upload_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        die("Gagal memindahkan file.");
    }

    // Simpan ke database
    require_once('../config/db.php');

    $sql = "INSERT INTO jobsdesc (mahasiswa_id, nama_file) VALUES (:mahasiswa_id, :nama_file)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mahasiswa_id' => $mahasiswa_id,
        ':nama_file' => $new_filename
    ]);

    // Redirect kembali ke mitra.php
    header("Location: mitra.php?upload=success");
    exit;
} else {
    // Jika bukan POST, langsung redirect
    header("Location: mitra.php");
    exit;
}
