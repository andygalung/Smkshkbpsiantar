<?php 
session_start(); 
include 'koneksi.php'; // Pastikan koneksi ke database  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Query untuk mencari user berdasarkan username
    $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect sesuai role
            if ($user['role'] == 'administrasi') {
                header("Location: dashboard_administrasi.php");
            } elseif ($user['role'] == 'bendahara') {
                header("Location: dashboard_bendahara.php");
            } elseif ($user['role'] == 'kepsek') {
                header("Location: dashboard_kepsek.php");
            }
            exit();
        } else {
            $error_message = "Password salah!";
        }
    } else {
        $error_message = "Username tidak ditemukan!";
    }
} 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Administrasi Pembayaran SMKS HKBP Siantar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --bg-gradient: linear-gradient(135deg, #2563eb, #3b82f6);
        }
        
        body, html {
            height: 100%;
            background: url('img/bglogin.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding: 20px;
        }
        
        .login-container {
            width: 420px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
            transform: translateY(0);
            transition: all 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }
        
        .header-section {
            background: var(--bg-gradient);
            padding: 30px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-section:before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: rotate 15s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #fff;
            padding: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            z-index: 5;
            margin: 0 auto;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }
        
        .login-title {
            color: white;
            margin-top: 15px;
            font-size: 24px;
            font-weight: 600;
            position: relative;
            z-index: 5;
        }
        
        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
            font-size: 14px;
            position: relative;
            z-index: 5;
        }
        
        .form-section {
    padding: 30px;
}

.form-floating {
    margin-bottom: 20px;
    position: relative;
}

.form-floating input {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 12px 15px 12px 45px;
    height: 55px;
    font-size: 15px;
    transition: all 0.3s;
    text-indent: 30px; /* This adds space at the beginning of the text */
}

.form-floating input:focus {
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    border-color: var(--primary-color);
}

.form-floating label {
    padding-left: 45px;
}

.input-icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 15px;
    color: #94a3b8;
    z-index: 10;
    pointer-events: none; /* Makes the icon not interfere with the input */
}
        
        .login-btn {
            height: 48px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            background: var(--bg-gradient);
            border: none;
            margin-top: 5px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .login-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.6s;
        }
        
        .login-btn:hover:before {
            left: 100%;
        }
        
        .login-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .forgot-password a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
        }
        
        .alert-shake {
            animation: shake 0.5s linear;
        }
        
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            50% { transform: translateX(10px); }
            75% { transform: translateX(-10px); }
            100% { transform: translateX(0); }
        }
        
        .form-control-focused {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1) !important;
        }
        
        .footer {
            text-align: center;
            padding: 10px 0;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="login-container">
            <div class="header-section">
                <div class="logo">
                    <img src="img/logo-smkshkbpsiantar.png" alt="Logo SMK SHK BPSiantar">
                </div>
                <h1 class="login-title">Administrator Portal</h1>
                <p class="login-subtitle">Sistem Administrasi Pembayaran</p>
            </div>
            
            <div class="form-section">
                <?php if(isset($error_message)): ?>
                <div class="alert alert-danger alert-shake" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <form action="index.php" method="POST" id="loginForm">
                    <div class="form-floating position-relative">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                        <label for="username">Username</label>
                    </div>
                    
                    <div class="form-floating position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary login-btn w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                </form>
                
                <div class="forgot-password">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        <i class="fas fa-question-circle me-1"></i>Lupa Password?
                    </a>
                </div>
            </div>
            
            <div class="footer">
                &copy; <?php echo date('Y'); ?> SMKS HKBP Siantar - Semua Hak Dilindungi
            </div>
        </div>
    </div>
    
    <!-- Modal Lupa Password -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Silakan hubungi administrator sistem untuk mereset password Anda.</p>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope me-3 text-primary" style="font-size: 20px;"></i>
                        <div>admin@smkshkbpsiantar.sch.id</div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-phone me-3 text-primary" style="font-size: 20px;"></i>
                        <div>0622-123456</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation and interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Focus effects for input fields
            const inputs = document.querySelectorAll('.form-control');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.classList.add('form-control-focused');
                    this.previousElementSibling.style.color = '#2563eb';
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.classList.remove('form-control-focused');
                        this.previousElementSibling.style.color = '#94a3b8';
                    }
                });
            });
            
            // Button ripple effect
            const loginBtn = document.querySelector('.login-btn');
            
            loginBtn.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 4px 12px rgba(37, 99, 235, 0.35)';
            });
            
            loginBtn.addEventListener('mouseleave', function() {
                this.style.boxShadow = 'none';
            });
            
            // Form submission animation
            const loginForm = document.getElementById('loginForm');
            
            loginForm.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    const invalidInputs = this.querySelectorAll(':invalid');
                    invalidInputs.forEach(input => {
                        input.classList.add('is-invalid');
                        setTimeout(() => {
                            input.classList.remove('is-invalid');
                        }, 3000);
                    });
                }
            });
        });
    </script>
</body>
</html>