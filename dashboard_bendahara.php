<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'bendahara') {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';
include 'header.php';

// Ambil data total siswa yang sudah membayar
$total_x = $conn->query("SELECT COUNT(*) AS total FROM pembayaran_x")->fetch_assoc()['total'];
$total_xi = $conn->query("SELECT COUNT(*) AS total FROM pembayaran_xi")->fetch_assoc()['total'];
$total_xii = $conn->query("SELECT COUNT(*) AS total FROM pembayaran_xii")->fetch_assoc()['total'];
$total_siswa = $total_x + $total_xi + $total_xii;

// Ambil total pendapatan per bulan dan per tahun
$pendapatan_bulanan = $conn->query("SELECT SUM(jumlah_bayar) AS total FROM (SELECT jumlah_bayar FROM pembayaran_x WHERE MONTH(tanggal_pembayaran) = MONTH(CURRENT_DATE()) UNION ALL SELECT jumlah_bayar FROM pembayaran_xi WHERE MONTH(tanggal_pembayaran) = MONTH(CURRENT_DATE()) UNION ALL SELECT jumlah_bayar FROM pembayaran_xii WHERE MONTH(tanggal_pembayaran) = MONTH(CURRENT_DATE())) AS pembayaran")->fetch_assoc()['total'];

$pendapatan_tahunan = $conn->query("SELECT SUM(jumlah_bayar) AS total FROM (SELECT jumlah_bayar FROM pembayaran_x WHERE YEAR(tanggal_pembayaran) = YEAR(CURRENT_DATE()) UNION ALL SELECT jumlah_bayar FROM pembayaran_xi WHERE YEAR(tanggal_pembayaran) = YEAR(CURRENT_DATE()) UNION ALL SELECT jumlah_bayar FROM pembayaran_xii WHERE YEAR(tanggal_pembayaran) = YEAR(CURRENT_DATE())) AS pembayaran")->fetch_assoc()['total'];

// Ambil total pengeluaran per bulan dan per tahun
$pengeluaran_bulanan = $conn->query("SELECT SUM(jumlah) AS total FROM pengeluaran WHERE MONTH(tanggal_pengeluaran) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_pengeluaran) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?: 0;

$pengeluaran_tahunan = $conn->query("SELECT SUM(jumlah) AS total FROM pengeluaran WHERE YEAR(tanggal_pengeluaran) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?: 0;

// Hitung saldo (pendapatan - pengeluaran)
$saldo_bulanan = $pendapatan_bulanan - $pengeluaran_bulanan;
$saldo_tahunan = $pendapatan_tahunan - $pengeluaran_tahunan;

$latest_payments = $conn->query("SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM (
    SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_x 
    UNION ALL 
    SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_xi 
    UNION ALL 
    SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_xii 
) AS all_payments ORDER BY tanggal_pembayaran DESC LIMIT 5");

// Ambil data pengeluaran terbaru
$latest_expenses = $conn->query("SELECT keterangan, jumlah, tanggal_pengeluaran FROM pengeluaran ORDER BY tanggal_pengeluaran DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bendahara</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .dashboard-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eaeaea;
        }
        .stats-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .stats-card:hover {
            transform: scale(1.05);
        }
        .stats-card .card-header {
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: none;
        }
        .stats-card .card-body {
            padding: 1.5rem;
        }
        .stats-card .card-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stats-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stats-icon-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #kelasDetails {
            display: none;
            transition: all 0.5s ease-in-out;
            padding-top: 1rem;
        }
        #kelasDetails ul {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 0;
        }
        #kelasDetails li {
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }
        #kelasDetails a:hover {
            text-decoration: underline !important;
        }
        .btn-logout {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }
        .action-button {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }
        /* Styling untuk tabel */
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            width: 80%;
            text-align: center;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 10px;
        }

        .table th, .table td {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background: #007bff;
            color: #fff;
        }

        .table tr:nth-child(even) {
            background: #f2f2f2;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eaeaea;
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
                width: 95%;
            }
            
            .table {
                display: block;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
  
    <div class="dashboard-container">
        <h2 class="dashboard-title">Dashboard Bendahara</h2>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3 stats-card">
                    <div class="card-header">Total Siswa yang Membayar</div>
                    <div class="card-body">
                        <div class="stats-icon-container">
                            <h3 class="card-title">
                                <a href="#" onclick="toggleKelas()" class="text-white text-decoration-none"><?php echo $total_siswa; ?> Siswa</a>
                            </h3>
                            <div class="icon"><i class="fas fa-users"></i></div>
                        </div>
                        <div id="kelasDetails">
                            <ul>
                                <li><a href="rekap_pembayaran_kelasx.php" class="text-white"><i class="fas fa-chevron-right"></i> Kelas X: <?php echo $total_x; ?> Siswa</a></li>
                                <li><a href="rekap_pembayaran_kelasxi.php" class="text-white"><i class="fas fa-chevron-right"></i> Kelas XI: <?php echo $total_xi; ?> Siswa</a></li>
                                <li><a href="rekap_pembayaran_kelasxii.php" class="text-white"><i class="fas fa-chevron-right"></i> Kelas XII: <?php echo $total_xii; ?> Siswa</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3 stats-card">
                    <div class="card-header">Pendapatan Bulanan</div>
                    <div class="card-body">
                        <div class="stats-icon-container">
                            <h3 class="card-title">Rp <?php echo number_format($pendapatan_bulanan, 0, ',', '.'); ?></h3>
                            <div class="icon"><i class="fas fa-calendar-check"></i></div>
                        </div>
                        <p class="card-text">Bulan ini</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3 stats-card">
                    <div class="card-header">Pendapatan Tahunan</div>
                    <div class="card-body">
                        <div class="stats-icon-container">
                            <h3 class="card-title">Rp <?php echo number_format($pendapatan_tahunan, 0, ',', '.'); ?></h3>
                            <div class="icon"><i class="fas fa-chart-line"></i></div>
                        </div>
                        <p class="card-text">
                            <a href="rekap_pendapatan_bulananbendahara.php" class="text-white text-decoration-none">
                                Lihat detail <i class="fas fa-arrow-right"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baris baru untuk informasi pengeluaran -->
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3 stats-card">
                    <div class="card-header">Pengeluaran Bulanan</div>
                    <div class="card-body">
                        <div class="stats-icon-container">
                            <h3 class="card-title">Rp <?php echo number_format($pengeluaran_bulanan, 0, ',', '.'); ?></h3>
                            <div class="icon"><i class="fas fa-file-invoice"></i></div>
                        </div>
                        <p class="card-text">Bulan ini</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3 stats-card">
                    <div class="card-header">Pengeluaran Tahunan</div>
                    <div class="card-body">
                        <div class="stats-icon-container">
                            <h3 class="card-title">Rp <?php echo number_format($pengeluaran_tahunan, 0, ',', '.'); ?></h3>
                            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                        </div>
                        <p class="card-text">
                            <a href="rekap_pengeluaran.php" class="text-white text-decoration-none">
                                Lihat detail <i class="fas fa-arrow-right"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card text-white bg-dark mb-3 stats-card">
                    <div class="card-header">Saldo Tahun Ini</div>
                    <div class="card-body">
                        <div class="stats-icon-container">
                            <h3 class="card-title">Rp <?php echo number_format($saldo_tahunan, 0, ',', '.'); ?></h3>
                            <div class="icon"><i class="fas fa-wallet"></i></div>
                        </div>
                        <p class="card-text">Pendapatan - Pengeluaran</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambah tombol untuk menambah pengeluaran -->
        <div class="row mb-4">
            <div class="col-12 text-end">
                <a href="tambah_pengeluaran.php" class="btn btn-danger">
                    <i class="fas fa-plus-circle"></i> Tambah Pengeluaran
                </a>
            </div>
        </div>
    </div>

    <!-- Tabel pembayaran terbaru -->
<div class="table-container">
    <h4 class="section-title">Riwayat Pembayaran Terbaru</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Jumlah Bayar</th>
                <th>Tanggal Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $latest_payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nama_siswa']; ?></td>
                    <td>Rp <?php echo number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['tanggal_pembayaran'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Tabel pengeluaran terbaru -->
<div class="table-container">
    <h4 class="section-title">Riwayat Pengeluaran Terbaru</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Tanggal Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($latest_expenses->num_rows > 0): ?>
                <?php while ($row = $latest_expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['keterangan']; ?></td>
                        <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengeluaran'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">Belum ada data pengeluaran</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleKelas() {
        let kelasDetails = document.getElementById("kelasDetails");
        kelasDetails.style.display = kelasDetails.style.display === "none" ? "block" : "none";
    }
</script>
<?php include('footer.php'); ?>   
</body>
</html>