<?php
require_once('../config/db.php'); // koneksi database
require_once('_session_check.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../login/index.php");
    exit;
}

$stmt = $pdo->query("
    SELECT logbook.*, users.username 
    FROM logbook 
    JOIN users ON logbook.mahasiswa_id = users.id 
    WHERE users.role = 'mahasiswa'
    ORDER BY logbook.tanggal ASC
");
$logbooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hadir = 0;
$tidak_hadir = 0;
foreach ($logbooks as $log) {
    if ($log['status'] === 'disetujui') {
        $hadir++;
    } else {
        $tidak_hadir++;
    }
}

// Tanggal real-time bahasa Indonesia
$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$hariIni = $hari[date('w')];
$tanggal = date('j');
$bulanIni = $bulan[date('n') - 1];
$tahun = date('Y');
$tanggalLengkap = "$hariIni, $tanggal $bulanIni $tahun";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Logbook - Magang TRPL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/admin/assets/img/logo/Polman-Babel.png">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
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
            margin-bottom: 25px;
        }
        
        .page-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .page-title h4 {
            font-size: 1.5rem;
            color: var(--dark);
        }
        
        .page-title i {
            color: var(--primary);
        }
        
        .date-display {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6c757d;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        
        .date-display i {
            color: var(--primary);
        }
        
        /* Top Section with Calendar and Stats */
        .top-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .calendar-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .calendar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .calendar-header h5 {
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .calendar-header i {
            color: var(--primary);
        }
        
        #calendar {
            height: 400px;
        }
        
        /* Stats Cards */
        .stats-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.success {
            border-left: 4px solid #28a745;
        }
        
        .stat-card.danger {
            border-left: 4px solid var(--danger);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .stat-title {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .stat-icon.success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .stat-icon.danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px 25px;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-header h5 {
            font-size: 1.1rem;
            margin: 0;
        }
        
        .table-header i {
            font-size: 1.1rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
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
            color: #6c757d;
            vertical-align: middle;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-badge.success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .status-badge.danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .top-section {
                grid-template-columns: 1fr;
            }
            
            .stats-container {
                flex-direction: row;
                justify-content: space-around;
            }
        }
        
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
            
            .stats-container {
                flex-direction: column;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 10px 15px;
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
        
        /* FullCalendar Custom Styles */
        .fc {
            font-family: 'Poppins', sans-serif;
        }
        
        .fc-header-toolbar {
            margin-bottom: 1.5em;
        }
        
        .fc-button-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .fc-button-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .fc-event {
            border-radius: 4px;
            font-size: 0.8rem;
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
                <li><a href="rekap_logbook.php" class="active"><i class="fas fa-book"></i> <span>Rekap Logbook</span></a></li>
                <li><a href="penilaian_dosen.php"><i class="fas fa-clipboard-check"></i> <span>Penilaian</span></a></li>
            
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
                <h3>Rekap Logbook Mahasiswa</h3>
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
            
            <div class="main fade-in">
                <div class="page-title">
                    <i class="fas fa-book"></i>
                    <h4>Rekap Logbook Mahasiswa</h4>
                </div>
                
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span><?= $tanggalLengkap ?></span>
                </div>
                
                <div class="top-section">
                    <div class="calendar-container fade-in" style="animation-delay: 0.1s">
                        <div class="calendar-header">
                            <i class="fas fa-calendar-alt"></i>
                            <h5>Kalender Kehadiran</h5>
                        </div>
                        <div id="calendar"></div>
                    </div>
                    
                    <div class="stats-container">
                        <div class="stat-card success fade-in" style="animation-delay: 0.2s">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-title">Total Hadir</div>
                                    <div class="stat-value"><?= $hadir ?></div>
                                </div>
                                <div class="stat-icon success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card danger fade-in" style="animation-delay: 0.3s">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-title">Total Tidak Hadir</div>
                                    <div class="stat-value"><?= $tidak_hadir ?></div>
                                </div>
                                <div class="stat-icon danger">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-container fade-in" style="animation-delay: 0.4s">
                <div class="table-header">
                    <i class="fas fa-list"></i>
                    <h5>Detail Kegiatan Logbook</h5>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Nama Mahasiswa</th>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-clock"></i> Hari</th>
                                <th><i class="fas fa-tasks"></i> Kegiatan</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logbooks as $log): 
                                $hari = date('l', strtotime($log['tanggal']));
                                $hariIndo = [
                                    'Monday' => 'Senin',
                                    'Tuesday' => 'Selasa',
                                    'Wednesday' => 'Rabu',
                                    'Thursday' => 'Kamis',
                                    'Friday' => 'Jumat',
                                    'Saturday' => 'Sabtu',
                                    'Sunday' => 'Minggu'
                                ][$hari] ?? $hari;
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($log['username']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($log['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($hariIndo) ?></td>
                                    <td><?= htmlspecialchars($log['kegiatan']) ?></td>
                                    <td>
                                        <?php if ($log['status'] === 'disetujui'): ?>
                                            <span class="status-badge success">
                                                <i class="fas fa-check"></i> Hadir
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge danger">
                                                <i class="fas fa-times"></i> Tidak Hadir
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set active menu
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
            
            // Initialize FullCalendar
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: [
                    <?php foreach ($logbooks as $log): ?>
                    {
                        title: <?= json_encode($log['status'] === 'disetujui' ? '✅ Hadir' : '❌ Tidak Hadir') ?>,
                        start: <?= json_encode($log['tanggal']) ?>,
                        color: <?= json_encode($log['status'] === 'disetujui' ? '#28a745' : '#f72585') ?>,
                        textColor: 'white'
                    },
                    <?php endforeach; ?>
                ],
                eventDisplay: 'block',
                dayMaxEvents: 3
            });
            calendar.render();
            
            // Hover effects for stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-3px)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>