<?php
require_once('../config/db.php');
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$row = $stmt->fetch();
$total_users = $row['total'];

$stmt_kriteria = $pdo->query("SELECT COUNT(*) as total FROM kriteria");
$row_kriteria = $stmt_kriteria->fetch();
$total_kriteria = $row_kriteria['total'];

$stmt_mahasiswa = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'");
$row_mahasiswa = $stmt_mahasiswa->fetch();
$total_mahasiswa = $row_mahasiswa['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magang TRPL - Admin</title>
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
        
        .stat-card-icon.users {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .stat-card-icon.kriteria {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .stat-card-icon.reports {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
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
        
        .recent-activity {
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
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1rem;
        }
        
        .activity-content {
            flex-grow: 1;
        }
        
        .activity-title {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: #6c757d;
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
                <li><a href="admin.php" class="active"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="admin_tambah_akun.php"><i class="fas fa-user-plus"></i> <span>Buat Akun</span></a></li>
                <li><a href="admin_kriteria.php"><i class="fas fa-clipboard-list"></i> <span>Kriteria Penilaian</span></a></li>
            
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
                <h3>Dashboard Admin</h3>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr($_SESSION['username'] ?? 'A', 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= $_SESSION['username'] ?? 'Admin' ?></div>
                    </div>
                </div>
            </div>
            
            <div class="main">
                <div class="welcome-section fade-in">
                    <h4>Selamat Datang Kembali!</h4>
                    <p>Anda login sebagai Administrator sistem Magang TRPL.</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Total Akun</div>
                                <div class="stat-card-value"><?= $total_users; ?></div>
                            </div>
                            <div class="stat-card-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <i class="fas fa-arrow-up text-success"></i> 12% dari bulan lalu
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.2s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Kriteria Penilaian</div>
                                <div class="stat-card-value"><?= $total_kriteria; ?></div>
                            </div>
                            <div class="stat-card-icon kriteria">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            Terakhir diperbarui: 2 hari lalu
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.3s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Peserta Aktif</div>
                                <div class="stat-card-value"><?= $total_mahasiswa; ?></div>
                            </div>
                            <div class="stat-card-icon reports">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            Sedang magang saat ini
                        </div>
                    </div>
                </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>