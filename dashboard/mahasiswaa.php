<?php
require_once('_session_check.php');
require_once('../config/db.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login/index.php");
    exit;
}
$mahasiswa_id = $_SESSION['id'];

// Query logbook disetujui
$stmtApproved = $pdo->prepare("SELECT COUNT(*) as total FROM logbook WHERE mahasiswa_id = :id AND status = 'disetujui'");
$stmtApproved->execute(['id' => $mahasiswa_id]);
$dataApproved = $stmtApproved->fetch();

// Query logbook belum disetujui
$stmtPending = $pdo->prepare("SELECT COUNT(*) as total FROM logbook WHERE mahasiswa_id = :id AND status != 'disetujui'");
$stmtPending->execute(['id' => $mahasiswa_id]);
$dataPending = $stmtPending->fetch();

// Query total logbook
$stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM logbook WHERE mahasiswa_id = :id");
$stmtTotal->execute(['id' => $mahasiswa_id]);
$dataTotal = $stmtTotal->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magang TRPL - Dashboard Mahasiswa</title>
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
        
        /* Main Dashboard Styles */
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.approved {
            border-left-color: var(--success);
        }
        
        .stat-card.pending {
            border-left-color: var(--warning);
        }
        
        .stat-card.total {
            border-left-color: var(--info);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card-icon.approved {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .stat-card-icon.pending {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        
        .stat-card-icon.total {
            background-color: rgba(114, 9, 183, 0.1);
            color: var(--info);
        }
        
        .stat-card-title {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .stat-card-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .stat-card-footer {
            margin-top: 10px;
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .quick-actions {
            margin-top: 30px;
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
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .action-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .action-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .action-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .action-card-icon.logbook {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .action-card-icon.report {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .action-card-icon.grade {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        
        .action-card-icon.jobs {
            background-color: rgba(114, 9, 183, 0.1);
            color: var(--info);
        }
        
        .action-card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }
        
        .action-card-desc {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* Progress Section */
        .progress-section {
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
        }
        
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 20px;
            height: 10px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, var(--primary), var(--success));
            height: 100%;
            border-radius: 20px;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            margin-top: 10px;
            font-weight: 500;
            color: var(--dark);
            text-align: center;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .action-grid {
                grid-template-columns: 1fr;
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
                <h2><i class="fas fa-graduation-cap"></i> <span>Magang TRPL</span></h2>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="mahasiswaa.php" class="active"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="jobsdes_mahasiswa.php"><i class="fas fa-briefcase"></i> <span>Jobs Desc</span></a></li>
                <li><a href="logbook.php"><i class="fas fa-book"></i> <span>Logbook</span></a></li>
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
                <h3>Dashboard Mahasiswa</h3>
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
            
            <div class="main">
                <div class="welcome-section fade-in">
                    <h4>Selamat Datang Kembali!</h4>
                    <p>Semangat menjalankan program magang TRPL. Tetap semangat dan terus belajar!</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card approved fade-in" style="animation-delay: 0.1s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Logbook Disetujui</div>
                                <div class="stat-card-value"><?= $dataApproved['total'] ?></div>
                            </div>
                            <div class="stat-card-icon approved">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <i class="fas fa-thumbs-up" style="color: var(--success);"></i> Bagus! Terus pertahankan
                        </div>
                    </div>
                    
                    <div class="stat-card pending fade-in" style="animation-delay: 0.2s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Logbook Pending</div>
                                <div class="stat-card-value"><?= $dataPending['total'] ?></div>
                            </div>
                            <div class="stat-card-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            Menunggu persetujuan mitra
                        </div>
                    </div>
                    
                    <div class="stat-card total fade-in" style="animation-delay: 0.3s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Total Logbook</div>
                                <div class="stat-card-value"><?= $dataTotal['total'] ?></div>
                            </div>
                            <div class="stat-card-icon total">
                                <i class="fas fa-list"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            Semua entry logbook yang dibuat
                        </div>
                    </div>
                </div>
                
                <?php 
                $totalProgress = $dataTotal['total'];
                $approvedProgress = $dataApproved['total'];
                $progressPercentage = $totalProgress > 0 ? ($approvedProgress / $totalProgress) * 100 : 0;
                ?>
                
                <div class="progress-section fade-in" style="animation-delay: 0.4s">
                    <h5 class="section-title">Progress Logbook</h5>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $progressPercentage ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <?= number_format($progressPercentage, 1) ?>% logbook telah disetujui (<?= $approvedProgress ?> dari <?= $totalProgress ?>)
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h5 class="section-title">Aksi Cepat</h5>
                    <div class="action-grid">
                        <div class="action-card fade-in" style="animation-delay: 0.5s">
                            <a href="logbook.php">
                                <div class="action-card-icon logbook">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="action-card-title">Tambah Logbook</div>
                                <div class="action-card-desc">Buat entry logbook harian baru</div>
                            </a>
                        </div>
                        
                        <div class="action-card fade-in" style="animation-delay: 0.6s">
                            <a href="laporan_akhir.php">
                                <div class="action-card-icon report">
                                    <i class="fas fa-file-upload"></i>
                                </div>
                                <div class="action-card-title">Upload Laporan</div>
                                <div class="action-card-desc">Submit laporan akhir magang</div>
                            </a>
                        </div>
                        
                        <div class="action-card fade-in" style="animation-delay: 0.7s">
                            <a href="nilaimagang.php">
                                <div class="action-card-icon grade">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="action-card-title">Lihat Nilai</div>
                                <div class="action-card-desc">Cek nilai dan evaluasi magang</div>
                            </a>
                        </div>
                        
                        <div class="action-card fade-in" style="animation-delay: 0.8s">
                            <a href="jobsdes_mahasiswa.php">
                                <div class="action-card-icon jobs">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="action-card-title">Job Description</div>
                                <div class="action-card-desc">Lihat tugas dan tanggung jawab</div>
                            </a>
                        </div>
                    </div>
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
            
            // Card hover effects
            const cards = document.querySelectorAll('.stat-card, .action-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    if (!card.classList.contains('action-card')) {
                        card.style.transform = 'translateY(-5px)';
                    }
                });
                
                card.addEventListener('mouseleave', () => {
                    if (!card.classList.contains('action-card')) {
                        card.style.transform = 'translateY(0)';
                    }
                });
            });
            
            // Animate progress bar
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                setTimeout(() => {
                    progressFill.style.width = progressFill.style.width;
                }, 500);
            }
        });
    </script>
</body>
</html>