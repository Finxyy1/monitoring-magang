<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login/index.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID mahasiswa tidak ditemukan.";
    exit;
}

$mahasiswaId = $_GET['id'];

// Tanggal real-time bahasa Indonesia
$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$hariIni = $hari[date('w')];
$tanggal = date('j');
$bulanIni = $bulan[date('n') - 1];
$tahun = date('Y');
$tanggalLengkap = "$hariIni, $tanggal $bulanIni $tahun";

// Ambil data mahasiswa
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$mahasiswaId]);
$mahasiswa = $stmt->fetch();

if (!$mahasiswa) {
    echo "Mahasiswa tidak ditemukan.";
    exit;
}

// Ambil kriteria internal
$kriteriaStmt = $pdo->prepare("SELECT id, nama_kriteria, bobot FROM kriteria");
$kriteriaStmt->execute();
$kriteriaList = $kriteriaStmt->fetchAll();

// Ambil nilai yang sudah ada (jika ada)
$nilaiStmt = $pdo->prepare("SELECT kriteria_id, nilai FROM nilai_magang WHERE users_id = ?");
$nilaiStmt->execute([$mahasiswaId]);
$existingNilai = $nilaiStmt->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['nilai'] as $kriteriaId => $nilai) {
        $stmt = $pdo->prepare("INSERT INTO nilai_magang (users_id, kriteria_id, nilai, pemberi_nilai) 
                               VALUES (?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), pemberi_nilai = VALUES(pemberi_nilai)");
        $stmt->execute([$mahasiswaId, $kriteriaId, $nilai, $_SESSION['username']]);
    }

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Nilai berhasil disimpan!',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#4361ee'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'penilaian_dosen.php';
                }
            });
        });
    </script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Nilai Mahasiswa - Dosen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/admin/assets/img/logo/Polman-Babel.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        /* Main Content Area */
        .main {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .page-title {
            margin-bottom: 30px;
        }
        
        .page-title h4 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .page-title p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .date-display {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark);
            margin-top: 15px;
            font-size: 1rem;
        }
        
        .date-display i {
            color: white;
        }
        
        /* Breadcrumb Navigation */
        .breadcrumb {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
        }
        
        .breadcrumb-list {
            display: flex;
            align-items: center;
            gap: 10px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .breadcrumb-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            color: #6c757d;
        }
        
        .breadcrumb-current {
            color: #6c757d;
            font-weight: 500;
        }
        
        /* Student Info Card */
        .student-info {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .student-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .student-details h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .student-details p {
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .student-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        /* Form Styles */
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .form-grid {
            display: grid;
            gap: 20px;
        }
        
        .form-group {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            transition: var(--transition);
        }
        
        .form-group:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
        }
        
        .form-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .form-input:valid {
            border-color: var(--success);
        }
        
        /* Action Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }
        
        /* Progress Indicator */
        .progress-section {
            margin-bottom: 30px;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .progress-text {
            font-weight: 500;
            color: var(--dark);
        }
        
        .progress-percentage {
            color: var(--primary);
            font-weight: 600;
        }
        
        .progress-bar {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--success) 100%);
            border-radius: 4px;
            transition: width 0.5s ease;
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
            
            .student-info {
                flex-direction: column;
                text-align: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
                <h2><i class="fas fa-chalkboard-teacher"></i> <span>Magang TRPL</span></h2>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dosen.php"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="rekap_logbook.php"><i class="fas fa-book"></i> <span>Rekap Logbook</span></a></li>
                <li><a href="penilaian_dosen.php" class="active"><i class="fas fa-clipboard-check"></i> <span>Penilaian</span></a></li>
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
                <h3>Beri Nilai Mahasiswa</h3>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr($_SESSION['username'] ?? 'D', 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= $_SESSION['username'] ?? 'Dosen' ?></div>
                    </div>
                    <div class="profile-role">Dosen Pembimbing</div>
                </div>
            </div>
            
            <div class="breadcrumb fade-in">
                <ul class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="penilaian_dosen.php">
                            <i class="fas fa-clipboard-check"></i>
                            Penilaian
                        </a>
                    </li>
                    <li class="breadcrumb-separator">
                        <i class="fas fa-chevron-right"></i>
                    </li>
                    <li class="breadcrumb-current">
                        Beri Nilai Mahasiswa
                    </li>
                </ul>
            </div>
            
            <div class="main">
                <!-- Student Info Card -->
                <div class="student-info fade-in" style="animation-delay: 0.1s">
                    <div class="student-avatar">
                        <?= substr($mahasiswa['username'], 0, 1) ?>
                    </div>
                    <div class="student-details">
                        <h3><?= htmlspecialchars($mahasiswa['username']) ?></h3>
                        <p>Mahasiswa Program Magang TRPL</p>
                        <div class="date-display" style="color: rgba(255,255,255,0.9);">
                            <i class="far fa-calendar-alt"></i>
                            <span><?= $tanggalLengkap ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Section -->
                <div class="progress-section fade-in" style="animation-delay: 0.2s">
                    <div class="progress-header">
                        <span class="progress-text">Progress Penilaian</span>
                        <span class="progress-percentage" id="progress-percent">0%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Form Section -->
                <form method="post" id="nilai-form">
                    <div class="form-section fade-in" style="animation-delay: 0.3s">
                        <h5 class="section-title">
                            <i class="fas fa-star"></i>
                            Kriteria Penilaian
                        </h5>
                        
                        <div class="form-grid">
                            <?php foreach ($kriteriaList as $index => $kriteria): ?>
                            <div class="form-group" style="animation-delay: <?= 0.4 + ($index * 0.1) ?>s">
                                <label class="form-label" for="nilai_<?= $kriteria['id'] ?>">
                                    <i class="fas fa-clipboard-list"></i>
                                    <?= htmlspecialchars($kriteria['nama_kriteria']) ?>
                                </label>
                                <div class="form-subtitle">
                                    Bobot: <?= $kriteria['bobot'] ?>% | Rentang Nilai: 0-100
                                </div>
                                <input 
                                    type="number" 
                                    name="nilai[<?= $kriteria['id'] ?>]" 
                                    id="nilai_<?= $kriteria['id'] ?>" 
                                    class="form-input nilai-input"
                                    min="0" 
                                    max="100" 
                                    value="<?= $existingNilai[$kriteria['id']] ?? '' ?>"
                                    placeholder="Masukkan nilai (0-100)"
                                    required>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-actions fade-in" style="animation-delay: 0.6s">
                        <a href="penilaian_dosen.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Progress tracking
            const nilaiInputs = document.querySelectorAll('.nilai-input');
            const progressFill = document.getElementById('progress-fill');
            const progressPercent = document.getElementById('progress-percent');
            
            function updateProgress() {
                let filledInputs = 0;
                nilaiInputs.forEach(input => {
                    if (input.value !== '') {
                        filledInputs++;
                    }
                });
                
                const percentage = Math.round((filledInputs / nilaiInputs.length) * 100);
                progressFill.style.width = percentage + '%';
                progressPercent.textContent = percentage + '%';
            }
            
            // Initial progress check
            updateProgress();
            
            // Add event listeners for progress tracking
            nilaiInputs.forEach(input => {
                input.addEventListener('input', updateProgress);
            });
            
            // Form validation
            const form = document.getElementById('nilai-form');
            form.addEventListener('submit', function(e) {
                let isValid = true;
                let emptyFields = [];
                
                nilaiInputs.forEach(input => {
                    if (input.value === '') {
                        isValid = false;
                        emptyFields.push(input.previousElementSibling.textContent.trim());
                        input.style.borderColor = '#f72585';
                    } else {
                        input.style.borderColor = '#4cc9f0';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Form Belum Lengkap!',
                        text: 'Mohon lengkapi semua kriteria penilaian.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#f8961e'
                    });
                    return false;
                }
                
                // Show loading
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Sedang menyimpan nilai mahasiswa',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            });
            
            // Remove active class from other menus and set penilaian as active
            const menuItems = document.querySelectorAll('.sidebar-menu li a');
            menuItems.forEach(item => {
                item.classList.remove('active');
            });
            const penilaianMenu = document.querySelector('a[href="penilaian_dosen.php"]');
            if (penilaianMenu) {
                penilaianMenu.classList.add('active');
            }
            
            // Input number formatting
            nilaiInputs.forEach(input => {
                input.addEventListener('input', function() {
                    let value = parseInt(this.value);
                    if (value > 100) {
                        this.value = 100;
                    } else if (value < 0) {
                        this.value = 0;
                    }
                });
            });
        });
    </script>
</body>
</html>