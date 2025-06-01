<?php
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mitra') {
    header("Location: ../login/index.php");
    exit;
}

require_once('../config/db.php');

// Ambil data mahasiswa
$stmt = $pdo->query("SELECT id, username FROM users WHERE role='mahasiswa'");
$mahasiswas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magang TRPL - Upload Jobsdesc</title>
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
        
        /* Form Card Styles */
        .card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }
        
        .card-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h4 {
            font-size: 1.3rem;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }
        
        .card-header h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
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
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: white;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        /* File Input Custom Style */
        .custom-file {
            position: relative;
            display: inline-block;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .custom-file-input {
            position: relative;
            z-index: 2;
            width: 100%;
            height: calc(2.25rem + 2px);
            margin: 0;
            opacity: 0;
        }
        
        .custom-file-label {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1;
            height: calc(2.25rem + 2px);
            padding: 0.5rem 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: none;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        
        .custom-file-label::after {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            z-index: 3;
            display: block;
            height: calc(calc(2.25rem + 2px) - 1px * 2);
            padding: 0.5rem 1rem;
            line-height: 1.5;
            color: white;
            content: "Browse";
            background-color: var(--primary);
            border-left: 1px solid #ddd;
            border-radius: 0 5px 5px 0;
            display: flex;
            align-items: center;
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
                <li><a href="mitrautama.php"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="mitra.php" class="active"><i class="fas fa-tasks"></i> <span>Upload Jobsdesc</span></a></li>
                <li><a href="logbook_mahasiswa.php"><i class="fas fa-book"></i> <span>Logbook Mahasiswa</span></a></li>
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
                <h3>Upload Jobsdesc</h3>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr($_SESSION['username'] ?? 'M', 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= $_SESSION['username'] ?? 'Mitra' ?></div>
                        <div class="profile-role">Mitra</div>
                    </div>
                </div>
            </div>
            
            <div class="card fade-in">
                <div class="card-header">
                    <h4>Form Upload Jobsdesc</h4>
                </div>
                <form action="upload_jobsdes.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="mahasiswa_id">Pilih Mahasiswa</label>
                        <select class="form-control" name="mahasiswa_id" id="mahasiswa_id" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php foreach ($mahasiswas as $mhs): ?>
                                <option value="<?= htmlspecialchars($mhs['id']) ?>"><?= htmlspecialchars($mhs['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="jobsdesc">File Jobsdesc (PDF/Gambar)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="jobsdesc" name="jobsdesc" accept=".pdf, image/*" required>
                            <label class="custom-file-label" for="jobsdesc">Pilih file...</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Upload Jobsdesc</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Update file input label with selected file name
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : "Pilih file...";
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });

        // Sidebar active menu item
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
        });
    </script>
</body>
</html>