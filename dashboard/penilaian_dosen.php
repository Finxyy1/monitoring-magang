<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login/index.php");
    exit;
}

// Tanggal real-time bahasa Indonesia
$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$hariIni = $hari[date('w')];
$tanggal = date('j');
$bulanIni = $bulan[date('n') - 1];
$tahun = date('Y');
$tanggalLengkap = "$hariIni, $tanggal $bulanIni $tahun";

// Ambil data mahasiswa
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'mahasiswa'");
$stmt->execute();
$mahasiswa = $stmt->fetchAll();

// Ambil status penilaian untuk setiap mahasiswa
$penilaian_status = [];
$sudah_dinilai_count = 0;
$detail_penilaian = [];

foreach ($mahasiswa as $mhs) {
    // Cek apakah mahasiswa sudah dinilai
    $stmt_check = $pdo->prepare("SELECT COUNT(*) as count FROM nilai_magang WHERE users_id = ?");
    $stmt_check->execute([$mhs['id']]);
    $result = $stmt_check->fetch();
    $status = $result['count'] > 0;
    $penilaian_status[$mhs['id']] = $status;
    
    // Jika sudah dinilai, ambil detail nilai
    if ($status) {
        $sudah_dinilai_count++;
        $stmt_detail = $pdo->prepare("
            SELECT AVG(nm.nilai) as rata_rata, COUNT(nm.id) as jumlah_kriteria,
                   MAX(nm.id) as last_update
            FROM nilai_magang nm 
            WHERE nm.users_id = ?
        ");
        $stmt_detail->execute([$mhs['id']]);
        $detail = $stmt_detail->fetch();
        $detail_penilaian[$mhs['id']] = $detail;
    }
}

// Count stats for summary cards
$totalMahasiswa = count($mahasiswa);
$belum_dinilai_count = $totalMahasiswa - $sudah_dinilai_count;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Mahasiswa - Dosen</title>
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
            color: var(--primary);
        }
        
        /* Stats Cards */
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
        
        .stat-card-icon.students {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .stat-card-icon.pending {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        
        .stat-card-icon.completed {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
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
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-top: 20px;
        }
        
        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
        }
        
        .table-title {
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .table-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            color: var(--dark);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            color: #333;
            font-size: 0.95rem;
        }
        
        tbody tr {
            transition: var(--transition);
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .action-btn {
            background-color: var(--primary);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }
        
        .action-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }
        
        .action-btn.edit {
            background-color: var(--warning);
        }
        
        .action-btn.edit:hover {
            background-color: #e6830a;
            box-shadow: 0 4px 8px rgba(248, 150, 30, 0.3);
        }
        
        .action-btn i {
            font-size: 0.8rem;
        }
        
        .nilai-badge {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 8px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }
        
        .empty-state h5 {
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 12px 15px;
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
                <h3>Penilaian Mahasiswa</h3>
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
            
            <div class="main">
                <div class="page-title fade-in">
                    <h4>Kelola Penilaian Mahasiswa</h4>
                    <p>Berikan penilaian dan evaluasi untuk mahasiswa bimbingan Anda.</p>
                    <div class="date-display">
                        <i class="far fa-calendar-alt"></i>
                        <span><?= $tanggalLengkap ?></span>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Total Mahasiswa</div>
                                <div class="stat-card-value"><?= $totalMahasiswa ?></div>
                            </div>
                            <div class="stat-card-icon students">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <i class="fas fa-users"></i> Mahasiswa bimbingan
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.2s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Belum Dinilai</div>
                                <div class="stat-card-value"><?= $belum_dinilai_count ?></div>
                            </div>
                            <div class="stat-card-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <i class="fas fa-exclamation-triangle"></i> Perlu penilaian
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.3s">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-card-title">Sudah Dinilai</div>
                                <div class="stat-card-value"><?= $sudah_dinilai_count ?></div>
                            </div>
                            <div class="stat-card-icon completed">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <i class="fas fa-thumbs-up"></i> Penilaian selesai
                        </div>
                    </div>
                </div>
                
                <div class="table-container fade-in" style="animation-delay: 0.4s">
                    <div class="table-header">
                        <h5 class="table-title">Daftar Mahasiswa Bimbingan</h5>
                        <p class="table-subtitle">Klik "Beri Nilai" untuk memberikan penilaian kepada mahasiswa atau "Edit Nilai" untuk mengubah penilaian</p>
                    </div>
                    
                    <div class="table-responsive">
                        <?php if (count($mahasiswa) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username Mahasiswa</th>
                                    <th>Status Penilaian</th>
                                    <th>Rata-rata Nilai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($mahasiswa as $mhs): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background-color: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.8rem;">
                                                <?= strtoupper(substr($mhs['username'], 0, 1)) ?>
                                            </div>
                                            <span><?= htmlspecialchars($mhs['username']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($penilaian_status[$mhs['id']]): ?>
                                            <span style="background-color: rgba(76, 201, 240, 0.1); color: var(--success); padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">
                                                <i class="fas fa-check-circle" style="font-size: 0.7rem;"></i> Sudah Dinilai
                                            </span>
                                            <?php if (isset($detail_penilaian[$mhs['id']])): ?>
                                                <span class="nilai-badge">
                                                    <?= $detail_penilaian[$mhs['id']]['jumlah_kriteria'] ?> kriteria
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="background-color: rgba(248, 150, 30, 0.1); color: var(--warning); padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">
                                                <i class="fas fa-clock" style="font-size: 0.7rem;"></i> Belum Dinilai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($penilaian_status[$mhs['id']] && isset($detail_penilaian[$mhs['id']])): ?>
                                            <span style="font-weight: 600; color: var(--success);">
                                                <?= number_format($detail_penilaian[$mhs['id']]['rata_rata'], 1) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="beri_nilai.php?id=<?= $mhs['id'] ?>" 
                                           class="action-btn <?= $penilaian_status[$mhs['id']] ? 'edit' : '' ?>">
                                            <i class="fas fa-<?= $penilaian_status[$mhs['id']] ? 'edit' : 'plus' ?>"></i>
                                            <?= $penilaian_status[$mhs['id']] ? 'Edit Nilai' : 'Beri Nilai' ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-graduate"></i>
                            <h5>Belum Ada Mahasiswa</h5>
                            <p>Belum ada mahasiswa yang terdaftar untuk dinilai.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Remove all active classes first
            const menuItems = document.querySelectorAll('.sidebar-menu li a');
            menuItems.forEach(item => {
                item.classList.remove('active');
            });
            
            // Set only the Penilaian menu as active
            const penilaianMenu = document.querySelector('a[href="penilaian_dosen.php"]');
            if (penilaianMenu) {
                penilaianMenu.classList.add('active');
            }
            
            // Add hover effects to cards
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
            
            // Add hover effects to action buttons
            const actionBtns = document.querySelectorAll('.action-btn');
            actionBtns.forEach(btn => {
                btn.addEventListener('mouseenter', () => {
                    btn.style.transform = 'translateY(-2px)';
                });
                
                btn.addEventListener('mouseleave', () => {
                    btn.style.transform = 'translateY(0)';
                });
            });
            
            // Add confirmation for logout
            const logoutBtn = document.querySelector('.logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    if (!confirm('Apakah Anda yakin ingin logout?')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>