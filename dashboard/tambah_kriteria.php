<?php
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

require_once('../config/db.php');

$errors = [];
$success = false;
$nama_kriteria = '';
$bobot = '';
$jenis = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kriteria = trim($_POST['nama_kriteria'] ?? '');
    $bobot = $_POST['bobot'] ?? '';
    $jenis = $_POST['jenis'] ?? '';

    if ($nama_kriteria === '') $errors[] = "Nama kriteria wajib diisi.";
    if (!is_numeric($bobot)) $errors[] = "Bobot harus berupa angka.";
    if (!in_array($jenis, ['internal', 'eksternal'])) $errors[] = "Jenis tidak valid.";

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO kriteria (nama_kriteria, bobot, jenis) VALUES (?, ?, ?)");
        $success = $stmt->execute([$nama_kriteria, $bobot, $jenis]);
        if ($success) {
            $nama_kriteria = '';
            $bobot = '';
            $jenis = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magang TRPL - Tambah Kriteria</title>
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
        
        /* Sidebar Styles - Same as dashboard */
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
        
        /* Main Content Area */
        .main {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 10px;
            position: relative;
            padding-bottom: 10px;
            text-align: center;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .page-subtitle {
            color: #6c757d;
            margin-bottom: 25px;
            font-size: 1rem;
            text-align: center;
        }
        
        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            border: 1px solid rgba(76, 201, 240, 0.3);
            color: var(--success);
        }
        
        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            border: 1px solid rgba(247, 37, 133, 0.3);
            color: var(--danger);
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .alert li {
            margin-bottom: 5px;
        }
        
        /* Form Styles */
        .form-container {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .form-label i {
            margin-right: 5px;
            color: var(--primary);
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
            background-color: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
            background-color: white;
            cursor: pointer;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        /* Button Styles */
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 2px 4px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.4);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        /* Success State */
        .success-container {
            text-align: center;
            padding: 40px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--success), #3a9bc1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        .success-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .success-message {
            color: #6c757d;
            margin-bottom: 25px;
        }
        
        .success-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        /* Responsive Design */
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
            
            .btn-group {
                flex-direction: column;
            }
            
            .main {
                margin: 0 10px;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .slide-in {
            animation: slideIn 0.5s ease forwards;
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
                <h3>Tambah Kriteria Baru</h3>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr($_SESSION['username'] ?? 'A', 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= $_SESSION['username'] ?? 'Admin' ?></div>
                    </div>
                </div>
            </div>
            
            <div class="main fade-in">
                <?php if ($success): ?>
                    <div class="success-container">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h4 class="success-title">Berhasil Disimpan!</h4>
                        <p class="success-message">Kriteria penilaian baru telah berhasil ditambahkan ke sistem.</p>
                        <div class="success-actions">
                            <a href="admin_kriteria.php" class="btn btn-primary">
                                <i class="fas fa-list"></i>
                                <span>Lihat Semua Kriteria</span>
                            </a>
                            <a href="tambah_kriteria.php" class="btn btn-secondary">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Lagi</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <h4 class="page-title">Tambah Kriteria Penilaian</h4>
                    <p class="page-subtitle">Masukkan informasi kriteria penilaian baru</p>
                    
                    <?php if ($errors): ?>
                        <div class="alert alert-danger slide-in">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Terjadi kesalahan:</strong>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-container slide-in" style="animation-delay: 0.2s">
                        <form method="post" autocomplete="off">
                            <div class="form-group">
                                <label for="nama_kriteria" class="form-label">
                                    <i class="fas fa-tag"></i>
                                    Nama Kriteria
                                </label>
                                <input 
                                    type="text" 
                                    id="nama_kriteria" 
                                    name="nama_kriteria" 
                                    class="form-input"
                                    value="<?= htmlspecialchars($nama_kriteria) ?>"
                                    placeholder="Masukkan nama kriteria..."
                                    required
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="bobot" class="form-label">
                                    <i class="fas fa-weight-hanging"></i>
                                    Bobot (%)
                                </label>
                                <input 
                                    type="number" 
                                    id="bobot" 
                                    name="bobot" 
                                    class="form-input"
                                    value="<?= htmlspecialchars($bobot) ?>"
                                    placeholder="Masukkan bobot dalam persen..."
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    required
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="jenis" class="form-label">
                                    <i class="fas fa-chart-bar"></i>
                                    Jenis Kriteria
                                </label>
                                <select id="jenis" name="jenis" class="form-select" required>
                                    <option value="">-- Pilih Jenis Kriteria --</option>
                                    <option value="internal" <?= $jenis === 'internal' ? 'selected' : '' ?>>Internal</option>
                                    <option value="eksternal" <?= $jenis === 'eksternal' ? 'selected' : '' ?>>Eksternal</option>
                                </select>
                            </div>
                            
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    <span>Simpan Kriteria</span>
                                </button>
                                <a href="admin_kriteria.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus pada input pertama
            const firstInput = document.querySelector('.form-input');
            if (firstInput) {
                firstInput.focus();
            }
            
            // Form validation enhancement
            const form = document.querySelector('form');
            const inputs = document.querySelectorAll('.form-input, .form-select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.checkValidity()) {
                        this.style.borderColor = 'var(--success)';
                    } else {
                        this.style.borderColor = 'var(--danger)';
                    }
                });
                
                input.addEventListener('input', function() {
                    this.style.borderColor = '#e9ecef';
                });
            });
            
            // Submit button loading state
            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Menyimpan...</span>';
                    submitBtn.disabled = true;
                    
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 3000);
                });
            }
        });
    </script>
</body>
</html>