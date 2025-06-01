<?php
require_once('_session_check.php');
require_once('../config/db.php');

$id = $_GET['id'] ?? null;
$errors = [];
$success = false;
$nama_kriteria = '';
$bobot = '';
$jenis = '';

if (!$id) {
    header("Location: kriteria.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM kriteria WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    $errors[] = "Data tidak ditemukan.";
} else {
    $nama_kriteria = $data['nama_kriteria'];
    $bobot = $data['bobot'];
    $jenis = $data['jenis'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kriteria = trim($_POST['nama_kriteria'] ?? '');
    $bobot = $_POST['bobot'] ?? '';
    $jenis = $_POST['jenis'] ?? '';

    if ($nama_kriteria === '') $errors[] = "Nama kriteria wajib diisi.";
    if (!is_numeric($bobot)) $errors[] = "Bobot harus berupa angka.";
    if (!in_array($jenis, ['internal', 'eksternal'])) $errors[] = "Jenis tidak valid.";

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE kriteria SET nama_kriteria = ?, bobot = ?, jenis = ? WHERE id = ?");
        $success = $stmt->execute([$nama_kriteria, $bobot, $jenis, $id]);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kriteria - Admin | Magang TRPL</title>
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
            background-color: var(--primary);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Form Styles */
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title::after {
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
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert ul {
            margin-bottom: 0;
            padding-left: 20px;
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
            
            .form-container {
                padding: 20px;
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
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-laptop-code"></i> <span>Magang TRPL</span></h2>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="admin.php"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="admin_tambah_akun.php"><i class="fas fa-user-plus"></i> <span>Buat Akun</span></a></li>
                <li><a href="admin_kriteria.php" class="active"><i class="fas fa-clipboard-list"></i> <span>Kriteria Penilaian</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i> <span>Kelola Peserta</span></a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> <span>Laporan</span></a></li>
                <li><a href="#"><i class="fas fa-cog"></i> <span>Pengaturan</span></a></li>
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
                <h3>Edit Kriteria Penilaian</h3>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr(htmlspecialchars($_SESSION['username'] ?? 'A'), 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
                    </div>
                    <div class="profile-role">Admin</div>
                </div>
            </div>
            
            <div class="form-container fade-in">
                <h4 class="page-title">Edit Kriteria</h4>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Data kriteria berhasil diperbarui. 
                        <a href="admin_kriteria.php" class="btn btn-secondary" style="margin-left: 10px; padding: 5px 10px; font-size: 0.8rem;">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                <?php else: ?>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" autocomplete="off">
                        <div class="form-group">
                            <label for="nama_kriteria" class="form-label">Nama Kriteria</label>
                            <input type="text" id="nama_kriteria" name="nama_kriteria" class="form-control" required 
                                   value="<?= htmlspecialchars($nama_kriteria) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="bobot" class="form-label">Bobot</label>
                            <input type="number" id="bobot" name="bobot" class="form-control" step="0.01" required 
                                   value="<?= htmlspecialchars($bobot) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis" class="form-label">Jenis Kriteria</label>
                            <select id="jenis" name="jenis" class="form-control" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="internal" <?= $jenis === 'internal' ? 'selected' : '' ?>>Internal</option>
                                <option value="eksternal" <?= $jenis === 'eksternal' ? 'selected' : '' ?>>Eksternal</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="admin_kriteria.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Tambahkan efek interaktif
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight menu aktif berdasarkan URL
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
            
            // Efek hover pada tombol
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', () => {
                    btn.style.transform = 'translateY(-2px)';
                });
                
                btn.addEventListener('mouseleave', () => {
                    btn.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>