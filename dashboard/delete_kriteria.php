<?php
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

require_once('../config/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM kriteria WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin_kriteria.php");
exit;
