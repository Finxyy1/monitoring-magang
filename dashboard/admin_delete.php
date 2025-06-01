<?php
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

require_once('../config/db.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: admin_tambah_akun.php");
    exit;
}

// Supaya aman, bisa tambahkan cek: jangan hapus akun admin terakhir, dll. Tapi ini dasar dulu:
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header("Location: admin_tambah_akun.php");
exit;
