<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}


// Ambil nama dan peran pengguna dari session
$nama_pengguna = $_SESSION['nama_pengguna'] ?? 'Pengguna';
$role = $_SESSION['role'] ?? 'User';

// Tetapkan warna avatar berdasarkan role
$avatar_colors = [
    'bendahara' => '#ff9800',
    'kepsek' => '#4caf50',
    'administrasi' => '#2196f3'
];
$avatar_bg = $avatar_colors[$role] ?? '#607d8b';
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMKS HKBP Siantar - Admin Header</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo {
            width: 90px;
            height: 60px;
            
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .logo img {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }
        
        .school-info {
            line-height: 1.3;
        }
        
        .school-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .school-address {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .search-box {
            position: relative;
            width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 8px 15px;
            padding-left: 35px;
            border-radius: 20px;
            border: none;
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .search-box input:focus {
            background-color: rgba(255, 255, 255, 0.25);
            outline: none;
        }
        
        .search-box svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid white;
        }
        
        .user-info {
            line-height: 1.2;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 600;
        }
        
        .user-role {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .notification {
            position: relative;
            cursor: pointer;
        }
        
        .notification svg {
            width: 22px;
            height: 22px;
        }
        
        .notification .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            background-color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            border: 2px solid #1e3a8a;
        }
        
        .nav-container {
            background-color: #f8fafc;
            padding: 0 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 15px 20px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .nav-link svg {
            width: 16px;
            height: 16px;
        }
        
        .nav-link:hover {
            color: #1e3a8a;
            background-color: rgba(59, 130, 246, 0.05);
        }
        
        .nav-link.active {
            color: #1e3a8a;
            border-bottom: 3px solid #1e3a8a;
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 15px;
            }
            
            .logo-container {
                margin-bottom: 15px;
            }
            
            .header-right {
                width: 100%;
                flex-direction: column;
                gap: 15px;
            }
            
            .search-box {
                width: 100%;
            }
            
            .nav-menu {
                overflow-x: auto;
                padding-bottom: 5px;
            }
            
            .nav-link {
                padding: 12px 15px;
                font-size: 13px;
            }
        }

        .dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: rgb(186, 39, 39);
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    min-width: 120px;
    text-align: center;
    padding: 10px 0;
    }

    .dropdown-menu a {
        display: block;
        padding: 10px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
    }

    .dropdown-menu a:hover {
        background:rgb(252, 0, 0);
    }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <div class="logo">
                <img src="img/logo-smkshkbpsiantar.png" alt="SMKS HKBP Siantar Logo">
            </div>
            <div class="school-info">
                <div class="school-name">SMKS HKBP SIANTAR</div>
                <div class="school-address">Jl. Pendidikan No. 123, Siantar, Sumatera Utara</div>
            </div>
        </div>
        
        <div class="header-right">
            <div class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" placeholder="Cari...">
            </div>
            
            
            
            <div class="user-profile" onclick="toggleDropdown()">
    <div class="avatar" style="background-color: <?php echo $avatar_bg; ?>;">
        <?php echo strtoupper(substr($nama_pengguna, 0, 1)); ?>
    </div>
    <div class="user-info">
        <div class="user-name"><?php echo htmlspecialchars($nama_pengguna); ?></div>
        <div class="user-role"><?php echo ucfirst($role); ?></div>
    </div>
    <div class="dropdown-menu" id="dropdown-menu">
        <a href="logout.php">Logout</a>
    </div>
</div>


        </div>
    </header>

    <script>
        function toggleDropdown() {
        var dropdown = document.getElementById("dropdown-menu");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }

    // Tutup dropdown jika klik di luar
    document.addEventListener("click", function (event) {
        var profile = document.querySelector(".user-profile");
        var dropdown = document.getElementById("dropdown-menu");
        
        if (!profile.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

    </script>
    
</body>
</html>