<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        session_regenerate_id(true);
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        switch ($user['role']) {
            case 'admin':
                header("Location: ../dashboard/admin.php");
                exit;
            case 'mahasiswa':
                header("Location: ../dashboard/mahasiswaa.php");
                exit;
            case 'dosen':
                header("Location: ../dashboard/dosen.php");
                exit;
            case 'mitra':
                header("Location: ../dashboard/mitrautama.php");
                exit;
            default:
                echo "Role tidak dikenali.";
                exit;
        }
    } else {
        header("Location: index.php?error=1");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
