<?php
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mitra') {
    header("Location: ../login/index.php");
    exit;
}

require_once('../config/db.php'); // koneksi PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = (int) $_POST['id'];

        // Update status menjadi approved
        $stmt = $pdo->prepare("UPDATE logbook SET status = 'disetujui' WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect kembali ke halaman logbook mitra atau dashboard
        header("Location: logbook_mahasiswa.php"); // ganti dengan halaman yang sesuai
        exit;
    } else {
        echo "ID logbook tidak valid.";
    }
} else {
    echo "Metode request tidak diizinkan.";
}
