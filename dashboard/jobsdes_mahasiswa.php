<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login/index.php");
    exit;
}

$mahasiswa_id = $_SESSION['id'] ?? 0;

// Ambil username mahasiswa
$stmtUser = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmtUser->execute([$mahasiswa_id]);
$mahasiswa = $stmtUser->fetch();

// Ambil file jobsdesc id dan cek keberadaan file
$stmtFiles = $pdo->prepare("SELECT id, nama_file FROM jobsdesc WHERE mahasiswa_id = ?");
$stmtFiles->execute([$mahasiswa_id]);
$jobsdescs = $stmtFiles->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobsdesc Mahasiswa - Magang TRPL</title>
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
        
        /* Main Content Card */
        .main {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .page-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-title i {
            color: var(--info);
        }
        
        .page-description {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 25px;
        }
        
        .student-info {
            background: linear-gradient(135deg, var(--primary), var(--info));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .student-info i {
            font-size: 2rem;
            opacity: 0.8;
        }
        
        .student-info-text {
            flex-grow: 1;
        }
        
        .student-info-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .student-info-name {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-top: 20px;
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary), var(--info));
            color: white;
            padding: 20px 25px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #f8f9fa;
            color: var(--dark);
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
        }
        
        td {
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-size: 0.95rem;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        /* File Link Styles */
        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--success), var(--primary));
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .file-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 201, 240, 0.4);
        }
        
        .file-link i {
            font-size: 1rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .empty-state p {
            font-size: 1rem;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* File Icon in Table */
        .file-icon {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .file-icon i {
            color: var(--info);
            font-size: 1.2rem;
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
            
            .student-info {
                flex-direction: column;
                text-align: center;
            }
            
            .main {
                padding: 20px;
            }
            
            th, td {
                padding: 12px 15px;
            }
        }
        
        @media (max-width: 480px) {
            .content {
                padding: 15px;
            }
            
            .main {
                padding: 15px;
            }
            
            .page-title {
                font-size: 1.5rem;
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
                <li><a href="mahasiswaa.php"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="jobsdes_mahasiswa.php" class="active"><i class="fas fa-briefcase"></i> <span>Jobs Desc</span></a></li>
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
                <h3>Job Description</h3>
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
            
            <div class="main fade-in">
                <div class="page-title">
                    <i class="fas fa-briefcase"></i>
                    Deskripsi Pekerjaan (Jobsdesc)
                </div>
                <div class="page-description">
                    Lihat dan unduh dokumen job description yang telah diberikan oleh mitra magang.
                </div>

                <div class="student-info">
                    <i class="fas fa-user-graduate"></i>
                    <div class="student-info-text">
                        <div class="student-info-label">Nama Mahasiswa:</div>
                        <div class="student-info-name"><?= htmlspecialchars($mahasiswa['username'] ?? '-') ?></div>
                    </div>
                </div>

                <?php if ($jobsdescs && count($jobsdescs) > 0): ?>
                    <div class="table-container">
                        <div class="table-header">
                            <i class="fas fa-list"></i>
                            Daftar File Job Description
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobsdescs as $index => $file): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <div class="file-icon">
                                                <i class="fas fa-file-pdf"></i>
                                                <?= htmlspecialchars($file['nama_file']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a class="file-link" href="download_jobsdes.php?id=<?= $file['id'] ?>" target="_blank" rel="noopener noreferrer">
                                                <i class="fas fa-external-link-alt"></i>
                                                Buka File
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h4>Belum Ada Job Description</h4>
                        <p>Job description belum diunggah oleh mitra magang. Silakan hubungi pembimbing atau tunggu informasi lebih lanjut dari mitra.</p>
                    </div>
                <?php endif; ?>
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