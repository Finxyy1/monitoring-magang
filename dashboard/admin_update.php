<?php
require_once('_session_check.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

require_once('../config/db.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: admin_tambah_akun.php");
    exit;
}

// Ambil data user berdasarkan id
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: admin_tambah_akun.php");
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // kosong = password tidak diubah
    $role = $_POST['role'] ?? '';

    if ($username === '') {
        $errors[] = "Username wajib diisi.";
    }

    if (!in_array($role, ['admin', 'mahasiswa', 'mitra', 'dosen'])) {
        $errors[] = "Role tidak valid.";
    }

    // Cek username unik kecuali untuk user yang sedang diedit
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Username sudah terdaftar.";
    }

    if (!$errors) {
        if ($password !== '') {
            // Update password dengan hash baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $password, $role, $id]);
        } else {
            // Update tanpa ubah password
            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $role, $id]);
        }
        $success = true;

        // Refresh data user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun - Admin</title>
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .breadcrumb {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
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
        
        /* Form Styles */
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-header h4 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .form-header p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .user-info-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
        }
        
        .user-info-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-info-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: #fff;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .password-note {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6169;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e68408;
            transform: translateY(-2px);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        /* Alert Styles */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            border: 1px solid rgba(76, 201, 240, 0.3);
            color: #0c5460;
        }
        
        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            border: 1px solid rgba(247, 37, 133, 0.3);
            color: #721c24;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .alert ul li {
            margin-bottom: 5px;
        }
        
        /* Role Badge */
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .role-admin { background-color: rgba(67, 97, 238, 0.1); color: var(--primary); }
        .role-mahasiswa { background-color: rgba(76, 201, 240, 0.1); color: var(--success); }
        .role-mitra { background-color: rgba(248, 150, 30, 0.1); color: var(--warning); }
        .role-dosen { background-color: rgba(247, 37, 133, 0.1); color: var(--danger); }
        
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
            .content {
                margin-left: 0;
                padding: 15px;
            }
            
            .sidebar {
                display: none;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
                <li><a href="admin_tambah_akun.php" class="active"><i class="fas fa-user-plus"></i> <span>Buat Akun</span></a></li>
                <li><a href="admin_kriteria.php"><i class="fas fa-clipboard-list"></i> <span>Kriteria Penilaian</span></a></li>
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
                <div>
                    <h3><i class="fas fa-user-edit"></i> Edit Akun Pengguna</h3>
                    <div class="breadcrumb">
                        <a href="admin.php">Dashboard</a> / 
                        <a href="admin_tambah_akun.php">Kelola Akun</a> / 
                        Edit Akun
                    </div>
                </div>
                <div class="profile">
                    <div class="profile-info">
                        <div class="profile-img">
                            <?= substr($_SESSION['username'] ?? 'A', 0, 1) ?>
                        </div>
                        <div class="profile-name"><?= $_SESSION['username'] ?? 'Admin' ?></div>
                    </div>
                </div>
            </div>
            
            <div class="form-container fade-in">
                <div class="form-header">
                    <h4><i class="fas fa-user-cog"></i> Edit Data Pengguna</h4>
                    <p>Perbarui informasi akun pengguna yang dipilih.</p>
                </div>

                <!-- User Info Card -->
                <div class="user-info-card">
                    <div class="user-info-title">
                        <i class="fas fa-info-circle"></i>
                        Informasi Akun Saat Ini
                    </div>
                    <div class="user-info-details">
                        <div><strong>ID:</strong> #<?= htmlspecialchars($user['id']) ?></div>
                        <div><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></div>
                        <div><strong>Role:</strong> <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>"><?= ucfirst(htmlspecialchars($user['role'])) ?></span></div>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Berhasil!</strong> Data akun telah berhasil diperbarui.
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Terjadi kesalahan:</strong>
                            <ul>
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="" method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username / Email
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            value="<?= htmlspecialchars($_POST['username'] ?? $user['username']) ?>"
                            placeholder="Masukkan username atau email"
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password Baru
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password baru (opsional)"
                        />
                        <div class="password-note">
                            <i class="fas fa-info-circle"></i>
                            Kosongkan jika tidak ingin mengubah password
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">
                            <i class="fas fa-user-tag"></i> Role Pengguna
                        </label>
                        <select id="role" name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" <?= (($_POST['role'] ?? $user['role']) === 'admin') ? 'selected' : '' ?>>
                                Administrator
                            </option>
                            <option value="mahasiswa" <?= (($_POST['role'] ?? $user['role']) === 'mahasiswa') ? 'selected' : '' ?>>
                                Mahasiswa
                            </option>
                            <option value="mitra" <?= (($_POST['role'] ?? $user['role']) === 'mitra') ? 'selected' : '' ?>>
                                Mitra Industri
                            </option>
                            <option value="dosen" <?= (($_POST['role'] ?? $user['role']) === 'dosen') ? 'selected' : '' ?>>
                                Dosen Pembimbing
                            </option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Akun
                        </button>
                        <a href="admin_tambah_akun.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight active menu
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.sidebar-menu li a');
            
            menuItems.forEach(item => {
                const itemHref = item.getAttribute('href');
                if (currentPage === itemHref || itemHref === 'admin_tambah_akun.php') {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
            
            // Form validation feedback
            const form = document.querySelector('form');
            const inputs = form?.querySelectorAll('input, select');
            
            inputs?.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.checkValidity()) {
                        this.style.borderColor = 'var(--success)';
                    } else if (this.value !== '') {
                        this.style.borderColor = 'var(--danger)';
                    }
                });
                
                input.addEventListener('input', function() {
                    this.style.borderColor = '#e9ecef';
                });
            });
            
            // Password field special handling
            const passwordField = document.getElementById('password');
            passwordField?.addEventListener('input', function() {
                if (this.value.length > 0 && this.value.length < 6) {
                    this.style.borderColor = 'var(--warning)';
                } else if (this.value.length >= 6) {
                    this.style.borderColor = 'var(--success)';
                } else {
                    this.style.borderColor = '#e9ecef';
                }
            });
        });
    </script>
</body>
</html>