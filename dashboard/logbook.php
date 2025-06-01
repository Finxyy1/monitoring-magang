<?php
require_once('_session_check.php');
require_once('../config/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login/index.php");
    exit;
}

$mahasiswa_id = $_SESSION['id'];

// Handle form submit logbook
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_logbook'])) {
    $tanggal = date('Y-m-d');
    $hari = date('l'); // bisa diubah ke bahasa Indonesia jika perlu
    $kegiatan = $_POST['kegiatan'] ?? '';

    // Cek apakah hari ini sudah ada logbook
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM logbook WHERE mahasiswa_id = ? AND tanggal = ?");
    $stmtCheck->execute([$mahasiswa_id, $tanggal]);
    $count = $stmtCheck->fetchColumn();

    if ($count > 0) {
        $error = "Anda sudah mengisi logbook hari ini.";
    } else {
        // Upload file bukti
        if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['file_bukti']['tmp_name'];
            $fileName = basename($_FILES['file_bukti']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['pdf','png','jpg','jpeg'];

            if (!in_array($fileExt, $allowed)) {
                $error = "Format file tidak didukung. Hanya PDF, PNG, JPG yang diperbolehkan.";
            } else {
                $uploadDir = '../uploads/logbook/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $newFileName = $mahasiswa_id . '_' . time() . '.' . $fileExt;
                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $uploadPath)) {
                    // Simpan ke DB
                    $stmtInsert = $pdo->prepare("INSERT INTO logbook (mahasiswa_id, tanggal, hari, kegiatan, file_bukti) VALUES (?, ?, ?, ?, ?)");
                    $stmtInsert->execute([$mahasiswa_id, $tanggal, $hari, $kegiatan, $newFileName]);
                    $success = "Logbook berhasil disimpan.";
                } else {
                    $error = "Gagal mengupload file.";
                }
            }
        } else {
            $error = "Harap melampirkan file bukti.";
        }
    }
}

// Ambil data logbook per minggu (7 hari terakhir)
$stmtLogs = $pdo->prepare("SELECT * FROM logbook WHERE mahasiswa_id = ? ORDER BY tanggal DESC");
$stmtLogs->execute([$mahasiswa_id]);
$logbooks = $stmtLogs->fetchAll();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magang TRPL - Logbook Mahasiswa</title>
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
        
        /* Main Content Styles */
        .main {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
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
        
        .logbook-container {
            display: flex;
            gap: 20px;
        }
        
        .logbook-form {
            flex: 1;
            max-width: 400px;
        }
        
        .logbook-history {
            flex: 2;
        }
        
        .date-info {
            background-color: var(--light);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
        }
        
        .date-info strong {
            color: var(--primary);
        }
        
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message.error {
            background-color: #fdecea;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .message.success {
            background-color: #e8f5e9;
            color: var(--secondary);
            border-left: 4px solid var(--secondary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        textarea {
            width: 100%;
            height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
            font-family: inherit;
            transition: var(--transition);
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .file-upload {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .file-upload-label:hover {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .file-upload-label i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .file-upload-label span {
            color: var(--primary);
            font-weight: 500;
        }
        
        .file-upload-label small {
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: var(--dark);
            display: none;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            text-align: center;
            transition: var(--transition);
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: var(--card-shadow);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            gap: 5px;
        }
        
        .edit-btn {
            background-color: var(--warning);
            color: white;
        }
        
        .edit-btn:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }
        
        .disabled-btn {
            background-color: #6c757d;
            color: white;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .view-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .view-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
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
            
            .logbook-container {
                flex-direction: column;
            }
            
            .logbook-form {
                max-width: 100%;
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
                <h2><i class="fas fa-graduation-cap"></i> <span>Magang TRPL</span></h2>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="mahasiswaa.php"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
                <li><a href="jobsdes_mahasiswa.php"><i class="fas fa-briefcase"></i> <span>Jobs Desc</span></a></li>
                <li><a href="logbook.php" class="active"><i class="fas fa-book"></i> <span>Logbook</span></a></li>
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
                <h3>Logbook Magang</h3>
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
                <div class="logbook-container">
                    <div class="logbook-form fade-in">
                        <h4 class="section-title"><i class="fas fa-book"></i> Isi Logbook Hari Ini</h4>
                        <div class="date-info">
                            <div>
                                <strong>Tanggal:</strong> <?= date('d M Y') ?>
                            </div>
                            <div>
                                <strong>Hari:</strong> <?= date('l') ?>
                            </div>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="message error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?= $error ?></span>
                            </div>
                        <?php elseif (!empty($success)): ?>
                            <div class="message success">
                                <i class="fas fa-check-circle"></i>
                                <span><?= $success ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="kegiatan">Kegiatan Harian:</label>
                                <textarea name="kegiatan" id="kegiatan" required placeholder="Deskripsikan kegiatan yang Anda lakukan hari ini..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Upload Bukti Kegiatan:</label>
                                <div class="file-upload">
                                    <label class="file-upload-label" for="file_bukti">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Klik untuk mengunggah file</span>
                                        <small>Format yang didukung: PDF, PNG, JPG (Maks. 5MB)</small>
                                    </label>
                                    <input type="file" class="file-upload-input" name="file_bukti" id="file_bukti" accept=".pdf,.png,.jpg,.jpeg" required />
                                    <div class="file-name" id="file-name"></div>
                                </div>
                            </div>

                            <button type="submit" name="submit_logbook" class="btn">
                                <i class="fas fa-paper-plane"></i> Kirim Logbook
                            </button>
                        </form>
                    </div>

                    <div class="logbook-history fade-in" style="animation-delay: 0.1s">
                        <h4 class="section-title"><i class="fas fa-history"></i> Riwayat Logbook</h4>
                        
                        <?php if ($logbooks): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Hari</th>
                                        <th>Kegiatan</th>
                                        <th>Bukti</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logbooks as $log): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(date('d M Y', strtotime($log['tanggal']))) ?></td>
                                            <td><?= htmlspecialchars($log['hari']) ?></td>
                                            <td><?= nl2br(htmlspecialchars(substr($log['kegiatan'], 0, 50) . (strlen($log['kegiatan']) > 50 ? '...' : ''))) ?></td>
                                            <td>
                                                <a href="../uploads/logbook/<?= htmlspecialchars($log['file_bukti']) ?>" target="_blank" class="view-link">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </td>
                                            <td>
                                                <span class="status <?= $log['status'] === 'disetujui' ? 'status-approved' : 'status-pending' ?>">
                                                    <?= $log['status'] === 'disetujui' ? 'Disetujui' : 'Pending' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($log['status'] === 'pending'): ?>
                                                    <a href="edit_logbook.php?id=<?= $log['id'] ?>" class="action-btn edit-btn">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                <?php else: ?>
                                                    <span class="action-btn disabled-btn" title="Logbook sudah disetujui">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-book-open"></i>
                                <h4>Belum ada logbook yang diisi</h4>
                                <p>Mulailah dengan mengisi logbook harian Anda</p>
                            </div>
                        <?php endif; ?>
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
            
            // Show selected file name
            document.getElementById('file_bukti').addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Tidak ada file dipilih';
                const fileNameElement = document.getElementById('file-name');
                fileNameElement.textContent = 'File terpilih: ' + fileName;
                fileNameElement.style.display = 'block';
            });

            // Card hover effects
            const cards = document.querySelectorAll('.action-btn, .btn');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-2px)';
                    card.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>