<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MAGANG TRPL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/admin/assets/img/logo/Polman-Babel.png">
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        padding: 20px;
    }

    .login-container {
        display: flex;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        min-height: 600px;
    }

    .login-left {
        flex: 1;
        padding: 40px;
        position: relative;
        background: white;
    }

    .logo-container {
        text-align: center;
        margin-bottom: 40px;
    }

    .logo {
        width: 220px;
        margin-bottom: 20px;
    }

    .welcome-text {
        color: #2a5298;
        font-size: 28px;
        margin-bottom: 10px;
    }

    .sub-text {
        color: #666;
        font-size: 14px;
    }

    .input-group {
        position: relative;
        margin-bottom: 25px;
    }

    .input-group .icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
    }

    .input-group input {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border: 2px solid #eee;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .input-group input:focus {
        border-color: #2a5298;
        box-shadow: 0 0 8px rgba(42, 82, 152, 0.2);
        outline: none;
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #888;
    }

    .login-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(42, 82, 152, 0.3);
    }

    .loader {
        display: none;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    @keyframes spin {
        0% { transform: translateX(-50%) rotate(0deg); }
        100% { transform: translateX(-50%) rotate(360deg); }
    }

    .login-btn.loading .btn-text {
        visibility: hidden;
    }

    .login-btn.loading .loader {
        display: block;
    }

    .additional-links {
        text-align: center;
        margin: 20px 0;
    }

    .forgot-password {
        color: #2a5298;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .forgot-password:hover {
        color: #1e3c72;
    }

    .login-right {
        flex: 1;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        position: relative;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
        color: white;
        text-align: center;
    }

    .animated-text {
        font-size: 32px;
        margin-bottom: 20px;
        animation: fadeInUp 1s ease;
    }

    .sub-animated-text {
        font-size: 18px;
        animation: fadeInUp 1s 0.3s backwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animated-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%);
        background-size: 40px 40px;
        animation: move 20s linear infinite;
        opacity: 0.1;
    }

    @keyframes move {
        0% { background-position: 0 0; }
        100% { background-position: 1000px 1000px; }
    }

    .logobawah {
        text-align: center;
        margin-top: 40px;
    }

    .footer-logo {
        width: 280px;
        opacity: 0.8;
    }

    @media (max-width: 768px) {
        .login-container {
            flex-direction: column;
        }
        
        .login-right {
            display: none;
        }
        
        .login-left {
            padding: 30px;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-left">
                <div class="logo-container">
                    <img src="../assets/admin/assets/img/logo/polanjuga.png" alt="Logo Kampus" class="logo">
                    <h2 class="welcome-text">SELAMAT DATANG</h2>
                    <p class="sub-text">Silakan masuk ke akun Anda</p>
                </div>
                
                <form action="proses_login.php" method="POST" class="login-form">
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input type="text" name="username" placeholder="Email/Username" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="password" placeholder="Password" required>
                        <i class="fas fa-eye-slash toggle-password" onclick="togglePassword()"></i>
                    </div>
                    <button type="submit" class="login-btn">
                        <span class="btn-text">Masuk</span>
                        <div class="loader"></div>
                    </button>
                    <div class="logobawah">
                        <img src="../assets/admin/assets/img/logo/logobawah.png" alt="Logo Bawah" class="footer-logo">
                    </div>
                </form>
            </div>
            
            <div class="login-right">
                <div class="overlay">
                    <h1 class="animated-text">MAGANG TRPL POLMAN BABEL</h1>
                    <p class="sub-animated-text"><strong>Politeknik Manufaktur Negeri Bangka Belitung</strong></p>
                    <div class="animated-background"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.querySelector('input[name="password"]');
            const eyeIcon = document.querySelector('.toggle-password');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        document.querySelector('.login-btn').addEventListener('click', function(e) {
            this.classList.add('loading');
            setTimeout(() => this.classList.remove('loading'), 2000);
        });
    </script>
</body>
</html>