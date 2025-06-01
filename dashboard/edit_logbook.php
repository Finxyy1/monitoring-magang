<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login/index.php");
    exit;
}

$mahasiswa_id = $_SESSION['id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: logbook_mahasiswa.php");
    exit;
}

// Ambil data logbook berdasarkan ID dan pastikan milik mahasiswa ini
$stmt = $pdo->prepare("SELECT * FROM logbook WHERE id = ? AND mahasiswa_id = ?");
$stmt->execute([$id, $mahasiswa_id]);
$logbook = $stmt->fetch();

if (!$logbook) {
    die("Logbook tidak ditemukan atau Anda tidak berhak mengedit.");
}

// Jika sudah disetujui, tidak boleh diedit
if ($logbook['status'] === 'disetujui') {
    die("Logbook sudah disetujui dan tidak bisa diedit.");
}

$error = '';
$success = '';

// Proses update logbook
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kegiatan = $_POST['kegiatan'] ?? '';

    // Jika ada upload file baru
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['file_bukti']['tmp_name'];
        $fileName = basename($_FILES['file_bukti']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['pdf','png','jpg','jpeg'];

        if (!in_array($fileExt, $allowed)) {
            $error = "Format file tidak didukung. Hanya PDF, PNG, JPG yang diperbolehkan.";
        } else {
            $uploadDir = '../uploads/logbook/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = $mahasiswa_id . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Hapus file lama jika ada
                if ($logbook['file_bukti'] && file_exists($uploadDir . $logbook['file_bukti'])) {
                    unlink($uploadDir . $logbook['file_bukti']);
                }
                $file_bukti_db = $newFileName;
            } else {
                $error = "Gagal mengupload file.";
            }
        }
    } else {
        // Jika tidak upload file baru, pakai file lama
        $file_bukti_db = $logbook['file_bukti'];
    }

    if (!$error) {
        // Update data logbook
       $stmtUpdate = $pdo->prepare("UPDATE logbook SET kegiatan = ?, file_bukti = ? WHERE id = ? AND mahasiswa_id = ?");
        $stmtUpdate->execute([$kegiatan, $file_bukti_db, $id, $mahasiswa_id]);

        $success = "Logbook berhasil diperbarui.";
        // Refresh data logbook setelah update
        $stmt = $pdo->prepare("SELECT * FROM logbook WHERE id = ? AND mahasiswa_id = ?");
        $stmt->execute([$id, $mahasiswa_id]);
        $logbook = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magang TRPL - Edit Logbook</title>
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
        
        /* Main Content Styles */
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
        
        /* Form Styles */
        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }
        
        .form-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--dark);
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .file-preview {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .file-preview a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .file-preview a:hover {
            text-decoration: underline;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
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
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> <span>Magang TRPL</span></h2>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="mahasiswaa.php"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="jobsdes_mahasiswa.php"><i class="fas fa-briefcase"></i> <span>Jobs Desc</span></a></li>
                <li><a href="logbook.php" class="active"><i class="fas fa-book"></i> <span>Logbook</span></a></li>
                <li><a href="laporan_akhir.php"><i class="fas fa-file-alt"></i> <span>Laporan Akhir</span></a></li>
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
                <h3>Edit Logbook</h3>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr($_SESSION['username'] ?? 'M', 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= $_SESSION['username'] ?? 'Mahasiswa' ?></div>
                    </div>
                    <div class="profile-role">Mahasiswa</div>
                </div>
            </div>
            
            <div class="form-container">
                <h2 class="form-title">Edit Logbook Tanggal <?= date('d M Y', strtotime($logbook['tanggal'])) ?></h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="kegiatan" class="form-label">Kegiatan</label>
                        <textarea name="kegiatan" id="kegiatan" class="form-control" required><?= htmlspecialchars($logbook['kegiatan']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">File Bukti Saat Ini</label>
                        <?php if ($logbook['file_bukti']): ?>
                            <div class="file-preview">
                                <i class="fas fa-paperclip"></i>
                                <a href="../uploads/logbook/<?= htmlspecialchars($logbook['file_bukti']) ?>" target="_blank">Lihat File</a>
                            </div>
                        <?php else: ?>
                            <p>Tidak ada file</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="file_bukti" class="form-label">Upload File Bukti Baru (PDF, PNG, JPG)</label>
                        <input type="file" name="file_bukti" id="file_bukti" class="form-control" accept=".pdf,.png,.jpg,.jpeg" />
                        <small>Kosongkan jika tidak ingin mengganti file</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="logbook.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
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
        });
    </script>
</body>
</html>