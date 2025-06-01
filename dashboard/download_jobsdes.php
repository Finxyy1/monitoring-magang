<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login/index.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    http_response_code(404);
    exit('File not found');
}

$stmt = $pdo->prepare("SELECT nama_file FROM jobsdesc WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    exit('File not found');
}

$file_path = __DIR__ . '/../uploads/jobsdesc/' . $file['nama_file'];

if (!file_exists($file_path)) {
    http_response_code(404);
    exit('File not found on server');
}

// Tentukan MIME type dari file berdasarkan extensi
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

header('Content-Type: ' . $mime_type);
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>
