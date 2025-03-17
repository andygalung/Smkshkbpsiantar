<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'kepsek') {
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

// Ambil data pengeluaran per bulan dan per tahun
$pengeluaran_bulanan = $conn->query("SELECT SUM(jumlah) AS total FROM pengeluaran 
    WHERE MONTH(tanggal_pengeluaran) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_pengeluaran) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?: 0;

$pengeluaran_tahunan = $conn->query("SELECT SUM(jumlah) AS total FROM pengeluaran 
    WHERE YEAR(tanggal_pengeluaran) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?: 0;

// Hitung saldo (selisih pendapatan dan pengeluaran)
$saldo_bulanan = $pendapatan_bulanan - $pengeluaran_bulanan;
$saldo_tahunan = $pendapatan_tahunan - $pengeluaran_tahunan;

// Ambil 5 pembayaran terbaru
$latest_payments = $conn->query("SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM (
    SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_x 
    UNION ALL 
    SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_xi 
    UNION ALL 
    SELECT nama_siswa, jumlah_bayar, tanggal_pembayaran FROM pembayaran_xii 
) AS all_payments ORDER BY tanggal_pembayaran DESC LIMIT 5");

// Ambil 5 pengeluaran terbaru
$latest_expenses = $conn->query("SELECT keterangan, jumlah, tanggal_pengeluaran FROM pengeluaran 
    ORDER BY tanggal_pengeluaran DESC LIMIT 5");

// Get monthly data for chart
$months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$currentYear = date('Y');

$monthlyIncome = [];
$monthlyExpense = [];

for ($i = 1; $i <= 12; $i++) {
    // Get monthly income
    $income_query = $conn->query("SELECT SUM(jumlah_bayar) AS total FROM (
        SELECT jumlah_bayar FROM pembayaran_x WHERE MONTH(tanggal_pembayaran) = $i AND YEAR(tanggal_pembayaran) = $currentYear
        UNION ALL 
        SELECT jumlah_bayar FROM pembayaran_xi WHERE MONTH(tanggal_pembayaran) = $i AND YEAR(tanggal_pembayaran) = $currentYear
        UNION ALL 
        SELECT jumlah_bayar FROM pembayaran_xii WHERE MONTH(tanggal_pembayaran) = $i AND YEAR(tanggal_pembayaran) = $currentYear
    ) AS pembayaran");
    $monthlyIncome[] = $income_query->fetch_assoc()['total'] ?: 0;
    
    // Get monthly expense
    $expense_query = $conn->query("SELECT SUM(jumlah) AS total FROM pengeluaran 
        WHERE MONTH(tanggal_pengeluaran) = $i AND YEAR(tanggal_pengeluaran) = $currentYear");
    $monthlyExpense[] = $expense_query->fetch_assoc()['total'] ?: 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Sekolah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4DD0E1;
            --warning-color: #FF9800;
            --danger-color: #F44336;
            --info-color: #4cc9f0;
            --dark-color: #1E293B;
            --light-color: #f8f9fa;
            --border-radius: 12px;
            --card-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
            --transition: all 0.3s ease;
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
            padding-bottom: 60px;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .dashboard-title:after {
            content: '';
            display: block;
            width: 40%;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
            margin-top: 5px;
        }

        .dashboard-subtitle {
            color: #6c757d;
            font-weight: 400;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stats-card .card-body {
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .stats-card .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        .stats-card .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-card .card-icon {
            position: absolute;
            top: 50%;
            right: 1.5rem;
            transform: translateY(-50%);
            font-size: 2.5rem;
            opacity: 0.2;
        }

        .stats-card::before {
            content: "";
            position: absolute;
            width: 210%;
            height: 100%;
            background: rgba(255, 255, 255, 0.08);
            transform: rotate(45deg);
            top: -30%;
            right: -80%;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .bg-success {
            background-color: var(--success-color) !important;
        }

        .bg-warning {
            background-color: var(--warning-color) !important;
        }

        .bg-danger {
            background-color: var(--danger-color) !important;
        }

        .bg-info {
            background-color: var(--info-color) !important;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-container {
            margin-top: 2rem;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table > thead > tr > th {
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            background-color: rgba(67, 97, 238, 0.05);
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.03);
        }

        .table tbody td {
            vertical-align: middle;
            border-color: #f0f0f0;
        }

        .chart-container {
            margin-top: 2rem;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .tab-container {
            margin-bottom: 1rem;
        }

        .nav-tabs {
            border-bottom: none;
            gap: 0.5rem;
        }

        .nav-tabs .nav-link {
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: #6c757d;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .tab-content {
            padding-top: 1rem;
        }

        .btn-view-all {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-view-all:hover {
            color: var(--secondary-color);
        }

        .btn-view-all i {
            font-size: 0.85rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        @media (max-width: 768px) {
            .container {
                padding: 0 0.75rem;
            }
            
            .dashboard-title {
                font-size: 1.5rem;
            }
            
            .stats-card .card-value {
                font-size: 1.5rem;
            }
            
            .tab-container {
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .nav-tabs {
                display: flex;
                flex-wrap: nowrap;
            }
            
            .chart-container {
                padding: 1rem;
            }
        }

        /* Status labels */
        .status-positive {
            color: #2ecc71
        }
        .status-positive {
            color: #2ecc71;
            font-weight: 600;
        }
        
        .status-negative {
            color: #e74c3c;
            font-weight: 600;
        }
        
        /* Print styles */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            
            .container {
                max-width: 100%;
                width: 100%;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .stats-card:hover, .card:hover {
                transform: none;
                box-shadow: none;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard Kepala Sekolah</h1>
            <p class="dashboard-subtitle">Lihat dan kelola laporan keuangan sekolah</p>
        </div>
        
        <div class="row">
            <!-- Total Siswa -->
            <div class="col-md-3 col-sm-6">
                <div class="stats-card card bg-primary text-white fade-in delay-1">
                    <div class="card-body">
                        <h5 class="card-title">Total Siswa Bayar</h5>
                        <h2 class="card-value"><?php echo number_format($total_siswa); ?></h2>
                        <p class="card-text">Siswa</p>
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pendapatan Bulanan -->
            <div class="col-md-3 col-sm-6">
                <div class="stats-card card bg-success text-white fade-in delay-2">
                    <div class="card-body">
                        <h5 class="card-title">Pendapatan Bulan Ini</h5>
                        <h2 class="card-value">Rp <?php echo number_format($pendapatan_bulanan); ?></h2>
                        <p class="card-text">Rupiah</p>
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pengeluaran Bulanan -->
            <div class="col-md-3 col-sm-6">
                <div class="stats-card card bg-warning text-white fade-in delay-3">
                    <div class="card-body">
                        <h5 class="card-title">Pengeluaran Bulan Ini</h5>
                        <h2 class="card-value">Rp <?php echo number_format($pengeluaran_bulanan); ?></h2>
                        <p class="card-text">Rupiah</p>
                        <div class="card-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Saldo Bulanan -->
            <div class="col-md-3 col-sm-6">
                <div class="stats-card card bg-info text-white fade-in delay-4">
                    <div class="card-body">
                        <h5 class="card-title">Saldo Bulan Ini</h5>
                        <h2 class="card-value">Rp <?php echo number_format($saldo_bulanan); ?></h2>
                        <p class="card-text">
                            <?php if($saldo_bulanan >= 0): ?>
                                <span class="status-positive"><i class="fas fa-arrow-up"></i> Surplus</span>
                            <?php else: ?>
                                <span class="status-negative"><i class="fas fa-arrow-down"></i> Defisit</span>
                            <?php endif; ?>
                        </p>
                        <div class="card-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
    <!-- Charts -->
    <div class="col-lg-8">
        <div class="card chart-container">
            <div class="chart-header">
                <h5 class="chart-title">Analisis Keuangan Tahunan <?php echo $currentYear; ?></h5>
            </div>
            <div style="height: 500px;">
                <canvas id="yearlyFinanceChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Financial Summary -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                Ringkasan Keuangan Tahunan
            </div>
            <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                            <span>Pendapatan Tahunan:</span>
                            <span class="fw-bold">Rp <?php echo number_format($pendapatan_tahunan); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Pengeluaran Tahunan:</span>
                            <span class="fw-bold">Rp <?php echo number_format($pengeluaran_tahunan); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Saldo Tahunan:</span>
                            <span class="fw-bold <?php echo $saldo_tahunan >= 0 ? 'status-positive' : 'status-negative'; ?>">
                                Rp <?php echo number_format($saldo_tahunan); ?>
                                <?php if($saldo_tahunan >= 0): ?>
                                    <i class="fas fa-arrow-up"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-down"></i>
                                <?php endif; ?>
                            </span>
                        </div>
            </div>
        </div>
        
        <!-- Class distribution -->
        <div class="card mt-4">
            <div class="card-header">
                Distribusi Pembayaran Per Kelas
            </div>
            <div class="card-body">
                <canvas id="classDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>
        <div class="row">
            <!-- Latest Payments -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Pembayaran Terbaru</span>
                        <a href="laporan_pembayaran.php" class="btn-view-all">
                           
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Siswa</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($payment = $latest_payments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['nama_siswa']); ?></td>
                                        <td>Rp <?php echo number_format($payment['jumlah_bayar']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($payment['tanggal_pembayaran'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Latest Expenses -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Pengeluaran Terbaru</span>
                        <a href="laporan_pengeluarankepsek.php" class="btn-view-all">
                            Lihat Semua <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Keterangan</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($expense = $latest_expenses->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($expense['keterangan']); ?></td>
                                        <td>Rp <?php echo number_format($expense['jumlah']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($expense['tanggal_pengeluaran'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart data
        const months = <?php echo json_encode($months); ?>;
        const monthlyIncome = <?php echo json_encode($monthlyIncome); ?>;
        const monthlyExpense = <?php echo json_encode($monthlyExpense); ?>;
        
        // Setup yearly finance chart
const yearlyFinanceCtx = document.getElementById('yearlyFinanceChart').getContext('2d');
const yearlyFinanceChart = new Chart(yearlyFinanceCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Pendapatan',
                data: monthlyIncome,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Pengeluaran',
                data: monthlyExpense,
                borderColor: '#f44336',
                backgroundColor: 'rgba(244, 67, 54, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true, // Changed to true
        aspectRatio: 2.5, // Add aspect ratio to control height vs width
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
        
        // Setup class distribution chart
        const classDistributionCtx = document.getElementById('classDistributionChart').getContext('2d');
        const classDistributionChart = new Chart(classDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Kelas X', 'Kelas XI', 'Kelas XII'],
                datasets: [{
                    data: [<?php echo $total_x; ?>, <?php echo $total_xi; ?>, <?php echo $total_xii; ?>],
                    backgroundColor: [
                        '#4361ee',
                        '#3f37c9',
                        '#4cc9f0'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} siswa (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Animation on scroll
        const fadeElements = document.querySelectorAll('.fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        fadeElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            observer.observe(el);
        });
    </script>

<?php include('footer.php'); ?>  
</body>
</html>