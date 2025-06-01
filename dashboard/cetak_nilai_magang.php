<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login/index.php");
    exit;
}

$mahasiswa_id = $_SESSION['id'];
$nama_mahasiswa = $_SESSION['username'] ?? 'Mahasiswa';

$stmt = $pdo->prepare("SELECT 
    n.id, n.nilai,
    k.nama_kriteria, k.bobot, k.jenis
FROM nilai_magang n
JOIN kriteria k ON n.kriteria_id = k.id
WHERE n.users_id = ?
");
$stmt->execute([$mahasiswa_id]);
$nilai_data = $stmt->fetchAll();

if (!$nilai_data) {
    die("Belum ada nilai yang bisa dicetak.");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai Magang - <?= htmlspecialchars($nama_mahasiswa) ?></title>
    
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #3498db; color: white; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <h1>Laporan Nilai Magang</h1>
    <p><strong>Nama Mahasiswa:</strong> <?= htmlspecialchars($nama_mahasiswa) ?></p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kriteria</th>
                <th>Bobot</th>
                <th>Jenis</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($nilai_data as $i => $n): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($n['nama_kriteria']) ?></td>
                <td><?= htmlspecialchars($n['bobot']) ?>%</td>
                <td><?= htmlspecialchars($n['jenis']) ?></td>
                <td><?= htmlspecialchars($n['nilai']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button onclick="window.print()">Cetak Halaman Ini</button>
</body>
</html>
