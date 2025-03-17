<?php session_start(); 
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'administrasi') {     
    header("Location: index.php");     
    exit(); 
} 


// Pastikan koneksi sudah di-include sebelumnya
include 'koneksi.php';
include 'header.php';

// Ambil data total siswa yang sudah membayar
$total_x = $conn->query("SELECT COUNT(*) AS total FROM pembayaran_x")->fetch_assoc()['total'];
$total_xi = $conn->query("SELECT COUNT(*) AS total FROM pembayaran_xi")->fetch_assoc()['total'];
$total_xii = $conn->query("SELECT COUNT(*) AS total FROM pembayaran_xii")->fetch_assoc()['total'];
$total_siswa = $total_x + $total_xi + $total_xii;

// Ambil total pendapatan per bulan dan per tahun
$pendapatan_bulanan = $conn->query("SELECT SUM(jumlah_bayar) AS total FROM (
    SELECT jumlah_bayar FROM pembayaran_x WHERE MONTH(tanggal_pembayaran) = MONTH(CURRENT_DATE()) 
    UNION ALL 
    SELECT jumlah_bayar FROM pembayaran_xi WHERE MONTH(tanggal_pembayaran) = MONTH(CURRENT_DATE()) 
    UNION ALL 
    SELECT jumlah_bayar FROM pembayaran_xii WHERE MONTH(tanggal_pembayaran) = MONTH(CURRENT_DATE())) AS pembayaran")->fetch_assoc()['total'];

$pendapatan_tahunan = $conn->query("SELECT SUM(jumlah_bayar) AS total FROM (
    SELECT jumlah_bayar FROM pembayaran_x WHERE YEAR(tanggal_pembayaran) = YEAR(CURRENT_DATE()) 
    UNION ALL 
    SELECT jumlah_bayar FROM pembayaran_xi WHERE YEAR(tanggal_pembayaran) = YEAR(CURRENT_DATE()) 
    UNION ALL 
    SELECT jumlah_bayar FROM pembayaran_xii WHERE YEAR(tanggal_pembayaran) = YEAR(CURRENT_DATE())) AS pembayaran")->fetch_assoc()['total'];

// Ambil total transaksi berhasil (hitung semua transaksi)
$total_transaksi_berhasil = $conn->query("SELECT COUNT(*) AS total FROM (
    SELECT id FROM pembayaran_x 
    UNION ALL 
    SELECT id FROM pembayaran_xi 
    UNION ALL 
    SELECT id FROM pembayaran_xii) AS transaksi")->fetch_assoc()['total'];


$latest_payments = $conn->query("
    SELECT id, nama_siswa, jumlah_bayar, tanggal_pembayaran 
    FROM (
        SELECT id, nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_x 
        UNION ALL 
        SELECT id, nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_xi 
        UNION ALL 
        SELECT id, nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_xii 
    ) AS all_payments 
    ORDER BY tanggal_pembayaran DESC 
    LIMIT 5
");
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrasi - SMKS HKBP Siantar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color:rgba(255, 255, 255, 0.65);
        }
        
        .dashboard-container {
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .welcome-text h2 {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .welcome-text p {
            color:rgb(0, 0, 0);
            margin-bottom: 0;
        }
        
        .dashboard-actions .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .card-icon i {
            font-size: 24px;
            color: white;
        }
        
        .card-stats h5 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .card-stats p {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 14px;
        }
        
        .bg-blue {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }
        
        .bg-green {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }
        
        .bg-amber {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
        }
        
        .bg-purple {
            background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 100%);
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            color: #495057;
        }
        
        .table td, .table th {
            padding: 0.75rem 1.25rem;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .badge-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .action-button {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            margin-right: 5px;
            transition: all 0.2s ease;
        }
        
        .action-button:hover {
            background-color: #3b82f6;
            color: white;
        }
        
        .quick-action-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .quick-action-card .card-body {
            padding-bottom: 10px;
        }
        
        .quick-action-card .btn {
            margin-top: 15px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .menu-card {
            text-align: center;
            padding: 20px;
        }
        
        .menu-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background-color: rgba(59, 130, 246, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            transition: all 0.2s ease;
        }
        
        .menu-icon i {
            font-size: 24px;
            color: #3b82f6;
        }
        
        .menu-card:hover .menu-icon {
            background-color: #3b82f6;
        }
        
        .menu-card:hover .menu-icon i {
            color: white;
        }
        
        .menu-card h5 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .menu-card p {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-text">
                <h2>Selamat Datang, <?php echo $_SESSION['username']; ?>!</h2>
                <p>Anda masuk sebagai <strong>Administrasi</strong> | <?php echo date('l, d F Y'); ?></p>
            </div>
            <div class="dashboard-actions">
                <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-2"></i> Filter
                </button>
                <div class="btn-group">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus me-2"></i> Tambah Baru
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="input_pembayaran.php">Pembayaran Baru</a></li>
                        <li><a class="dropdown-item" href="#">Siswa Baru</a></li>
                        <li><a class="dropdown-item" href="#">Tagihan Baru</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Statistik Cards -->
        <div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-icon bg-blue">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="card-stats">
                    <h5>Rp <?php echo number_format($pendapatan_bulanan, 0, ',', '.'); ?></h5>
                    <p>Total Pendapatan Bulan Ini</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-icon bg-green">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="card-stats">
                    <h5><?php echo $total_transaksi_berhasil; ?></h5>
                    <p>Transaksi Berhasil</p>
                </div>
            </div>
        </div>
    </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-icon bg-amber">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="card-stats">
                            <h5>0</h5>
                            <p>Siswa Belum Lunas</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-icon bg-purple">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="card-stats">
                            <h5>0</h5>
                            <p>Laporan Menunggu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Access Menu -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3 col-lg-2">
                <a href="input_pembayaran.php" class="card menu-card h-100">
                    <div class="menu-icon">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <h5>Input Pembayaran</h5>
                    <p>Catat pembayaran siswa</p>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="card menu-card h-100">
                    <div class="menu-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h5>Data Siswa</h5>
                    <p>Kelola data siswa</p>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="card menu-card h-100">
                    <div class="menu-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h5>Tagihan</h5>
                    <p>Kelola tagihan bulanan</p>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="card menu-card h-100">
                    <div class="menu-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h5>Laporan</h5>
                    <p>Lihat & unduh laporan</p>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="card menu-card h-100">
                    <div class="menu-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h5>Notifikasi</h5>
                    <p>Buat pengumuman</p>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="card menu-card h-100">
                    <div class="menu-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h5>Pengaturan</h5>
                    <p>Konfigurasi sistem</p>
                </a>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                        
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Siswa</th>
                                        <th>Jumlah</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $latest_payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['tanggal_pembayaran']))); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                                            <td>Rp <?php echo number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                                            
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Features -->
        <div class="row g-3">
            <!-- Outstanding Payments -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pembayaran Tertunggak</h5>
                        <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                        <th>Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Hendra Limbong</td>
                                        <td>XII TKJ 1</td>
                                        <td>SPP (2 bulan)</td>
                                        <td><span class="status-badge badge-danger">Tertunggak</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Ingatkan</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Rina Simbolon</td>
                                        <td>XI AKL 2</td>
                                        <td>SPP (1 bulan)</td>
                                        <td><span class="status-badge badge-danger">Tertunggak</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Ingatkan</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Daniel Hutabarat</td>
                                        <td>X RPL 3</td>
                                        <td>Praktikum</td>
                                        <td><span class="status-badge badge-danger">Tertunggak</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Ingatkan</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Maria Hutasoit</td>
                                        <td>XI OTKP 1</td>
                                        <td>Uang Bangunan</td>
                                        <td><span class="status-badge badge-warning">Cicilan</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Ingatkan</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions and Reports -->
            <div class="col-lg-6">
                <div class="row g-3">
                    <!-- Upcoming Deadlines -->
                    <div class="col-md-12">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Jatuh Tempo Mendatang</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0">SPP April 2025</h6>
                                        <small class="text-muted">Untuk semua siswa</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning text-dark">15 hari lagi</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0">Pembayaran Praktikum Semester II</h6>
                                        <small class="text-muted">Kelas X dan XI</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-danger">5 hari lagi</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Pembayaran Study Tour</h6>
                                        <small class="text-muted">Kelas XII</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">28 hari lagi</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="col-md-6">
                        <div class="card h-100 quick-action-card">
                            <div class="card-body">
                                <h5 class="card-title">Kirim Pengingat</h5>
                                <p class="card-text">Kirim notifikasi pengingat pembayaran kepada siswa atau orang tua.</p>
                                <a href="#" class="btn btn-primary w-100">Kirim Sekarang</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="col-md-6">
                        <div class="card h-100 quick-action-card">
                            <div class="card-body">
                                <h5 class="card-title">Laporan Keuangan</h5>
                                <p class="card-text">Unduh laporan keuangan bulanan atau per jenis pembayaran.</p>
                                <a href="#" class="btn btn-primary w-100">Buat Laporan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="periodFilter" class="form-label">Periode</label>
                            <select class="form-select" id="periodFilter">
                                <option selected>Pilih periode</option>
                                <option value="today">Hari Ini</option>
                                <option value="this_week">Minggu Ini</option>
                                <option value="this_month">Bulan Ini</option>
                                <option value="last_month">Bulan Lalu</option>
                                <option value="custom">Kustom</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="paymentTypeFilter" class="form-label">Jenis Pembayaran</label>
                            <select class="form-select" id="paymentTypeFilter">
                                <option selected>Semua Jenis</option>
                                <option value="spp">SPP</option>
                                <option value="building">Uang Bangunan</option>
                                <option value="practicum">Praktikum</option>
                                <option value="uniform">Seragam</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option selected>Semua Status</option>
                                <option value="paid">Lunas</option>
                                <option value="partial">Cicilan</option>
                                <option value="unpaid">Belum Lunas</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="classFilter" class="form-label">Kelas</label>
                            <select class="form-select" id="classFilter">
                                <option selected>Semua Kelas</option>
                                <option value="x">Kelas X</option>
                                <option value="xi">Kelas XI</option>
                                <option value="xii">Kelas XII</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Optional JavaScript for additional functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // For demonstration - you can add more JavaScript here
        });
    </script>

<?php include('footer.php'); ?>   
</body>
</html>