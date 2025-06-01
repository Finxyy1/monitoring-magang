<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login/index.php");
    exit;
}

$mahasiswa_id = $_SESSION['id'];
$nama_mahasiswa = $_SESSION['username'] ?? 'Mahasiswa';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_laporan'])) {
    if (isset($_FILES['laporan_file']) && $_FILES['laporan_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['laporan_file']['tmp_name'];
        $fileName = $_FILES['laporan_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExt !== 'pdf') {
            $error = "Format file tidak didukung. Hanya PDF yang diperbolehkan.";
        } else {
            $fileData = file_get_contents($fileTmp);
            $tanggal_upload = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare("INSERT INTO laporan (mahasiswa_id, laporan_file, tanggal_upload) VALUES (?, ?, ?)");
            $stmt->bindParam(1, $mahasiswa_id);
            $stmt->bindParam(2, $fileData, PDO::PARAM_LOB);
            $stmt->bindParam(3, $tanggal_upload);

            if ($stmt->execute()) {
                $success = "Laporan akhir berhasil diupload.";
            } else {
                $error = "Gagal menyimpan laporan ke database.";
            }
        }
    } else {
        $error = "Harap melampirkan file laporan PDF.";
    }
}

$stmt = $pdo->prepare("SELECT id, tanggal_upload FROM laporan WHERE mahasiswa_id = ? ORDER BY tanggal_upload DESC");
$stmt->execute([$mahasiswa_id]);
$laporans = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magang TRPL - Laporan Akhir</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/admin/assets/img/logo/Polman-Babel.png">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --danger: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
            --info: #7209b7;
            --dark: #212529;
            --light: #f8f9fa;
            --sidebar-bg: #2b2d42;
            --sidebar-active: #3a56d4;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body, html {
            font-family: 'Poppins', sans-serif;
            height: 100%;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: white;
            padding: 20px 0;
            transition: var(--transition);
            position: fixed;
            height: 100%;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-header h2 i {
            color: var(--primary);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0 15px;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
            position: relative;
        }
        
        .sidebar-menu li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
        }
        
        .sidebar-menu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-menu li a.active {
            background-color: var(--sidebar-active);
            color: white;
            font-weight: 500;
        }
        
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }
        
        .logout-btn {
            margin: 20px;
            padding: 12px;
            width: calc(100% - 40px);
            background-color: var(--danger);
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background-color: #d91a6a;
            transform: translateY(-2px);
        }
        
        .content {
            flex-grow: 1;
            margin-left: 250px;
            padding: 20px;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }
        
        .header h3 {
            font-size: 1.4rem;
            color: var(--dark);
        }
        
        .profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .profile-name {
            font-weight: 500;
        }
        
        .profile-role {
            background-color: var(--success);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Main Content Styles */
        .main {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .welcome-section {
            margin-bottom: 30px;
        }
        
        .welcome-section h4 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .welcome-section p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .upload-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        
        .upload-section h5 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }
        
        .upload-section h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .file-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .file-input input[type="file"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn i {
            font-size: 0.9rem;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .message.error {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .message.success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .table-container {
            overflow-x: auto;
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: var(--card-shadow);
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .download-btn {
            background-color: var(--success);
            color: white;
        }
        
        .download-btn:hover {
            background-color: #3ab4d9;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .empty-state h5 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .sidebar-header h2 span,
            .sidebar-menu li a span {
                display: none;
            }
            
            .sidebar-menu li a {
                justify-content: center;
                padding: 12px 0;
            }
            
            .sidebar-menu li a i {
                font-size: 1.2rem;
            }
            
            .logout-btn span {
                display: none;
            }
            
            .content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .profile {
                width: 100%;
                justify-content: space-between;
            }
            
            .file-input {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .file-input input[type="file"] {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            th, td {
                padding: 10px 5px;
                font-size: 0.9rem;
            }
            
            .action-btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> <span>Magang TRPL</span></h2>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="mahasiswaa.php"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="jobsdes_mahasiswa.php"><i class="fas fa-briefcase"></i> <span>Jobs Desc</span></a></li>
                <li><a href="logbook.php"><i class="fas fa-book"></i> <span>Logbook</span></a></li>
                <li><a href="laporan_akhir.php" class="active"><i class="fas fa-file-alt"></i> <span>Laporan Akhir</span></a></li>
                <li><a href="nilaimagang.php"><i class="fas fa-star"></i> <span>Nilai Magang</span></a></li>
            </ul>
            
            <form action="../login/logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
        
        <!-- Main Content Area -->
        <div class="content">
            <div class="header">
                <h3>Laporan Akhir Magang</h3>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr($nama_mahasiswa, 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= htmlspecialchars($nama_mahasiswa) ?></div>
                    </div>
                    <div class="profile-role">Mahasiswa</div>
                </div>
            </div>
            
            <div class="main fade-in">
                <div class="welcome-section">
                    <h4>Laporan Akhir Magang</h4>
                    <p>Upload laporan akhir magang Anda dalam format PDF. Pastikan laporan sudah lengkap sebelum diupload.</p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="message error fade-in"><?= htmlspecialchars($error) ?></div>
                <?php elseif (!empty($success)): ?>
                    <div class="message success fade-in"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <div class="upload-section fade-in" style="animation-delay: 0.1s">
                    <h5>Upload Laporan Baru</h5>
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="laporan_file">File Laporan (PDF)</label>
                            <div class="file-input">
                                <input type="file" name="laporan_file" id="laporan_file" accept=".pdf" required>
                            </div>
                        </div>
                        <button type="submit" name="submit_laporan" class="btn">
                            <i class="fas fa-upload"></i> Upload Laporan
                        </button>
                    </form>
                </div>
                
                <div class="table-container fade-in" style="animation-delay: 0.2s">
                    <h5 style="margin-bottom: 20px; color: var(--dark);">Riwayat Upload Laporan</h5>
                    
                    <?php if ($laporans): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($laporans as $index => $lap): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= date('d M Y H:i', strtotime($lap['tanggal_upload'])) ?></td>
                                        <td>
                                            <a href="download_laporan.php?id=<?= $lap['id'] ?>" class="action-btn download-btn" target="_blank">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h5>Belum ada laporan yang diupload</h5>
                            <p>Silahkan upload laporan akhir magang Anda</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Active menu highlighting
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.sidebar-menu li a');
            
            menuItems.forEach(item => {
                const itemHref = item.getAttribute('href');
                if (currentPage === itemHref) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
            
            // File input styling
            const fileInput = document.getElementById('laporan_file');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const fileName = this.files[0]?.name || 'Pilih file';
                    const label = this.previousElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        label.textContent = fileName;
                    }
                });
            }
        });
    </script>
</body>
</html>