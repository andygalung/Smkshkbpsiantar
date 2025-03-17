<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'bendahara') {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';

// Set default filters
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get list of available years for filter
$years_query = $conn->query("SELECT DISTINCT YEAR(tanggal_pengeluaran) as year FROM pengeluaran ORDER BY year DESC");
$years = [];
while ($year_row = $years_query->fetch_assoc()) {
    $years[] = $year_row['year'];
}

// Build query with filters
$query = "SELECT * FROM pengeluaran WHERE 1=1";

if (!empty($tahun)) {
    $query .= " AND YEAR(tanggal_pengeluaran) = '$tahun'";
}

if (!empty($bulan)) {
    $query .= " AND MONTH(tanggal_pengeluaran) = '$bulan'";
}

if (!empty($search)) {
    $query .= " AND (keterangan LIKE '%$search%')";
}

$query .= " ORDER BY tanggal_pengeluaran DESC";

$result = $conn->query($query);

// Get total for current filter
$total_query = str_replace("SELECT *", "SELECT SUM(jumlah) as total", $query);
$total_result = $conn->query($total_query)->fetch_assoc();
$total_pengeluaran = $total_result['total'] ?: 0;

// Get nama bulan
function getNamaBulan($bulan) {
    $nama_bulan = [
        '1' => 'Januari',
        '2' => 'Februari',
        '3' => 'Maret',
        '4' => 'April',
        '5' => 'Mei',
        '6' => 'Juni',
        '7' => 'Juli',
        '8' => 'Agustus',
        '9' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];
    return isset($nama_bulan[$bulan]) ? $nama_bulan[$bulan] : '';
}

// Handle export to PDF
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    // Set nama file
    $periode = '';
    if (!empty($tahun)) {
        $periode .= "Tahun $tahun";
    }
    if (!empty($bulan)) {
        $periode .= " Bulan " . getNamaBulan($bulan);
    }
    
    $filename = "Rekap_Pengeluaran_$periode.pdf";
    
    // Redirect to PDF generator
    header("Location: generate_pdf_pengeluaran.php?tahun=$tahun&bulan=$bulan&search=$search&filename=" . urlencode($filename));
    exit();
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pengeluaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4DD0E1;
            --warning-color: #FF9800;
            --danger-color: #F44336;
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

        /* Dashboard Components */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0;
            position: relative;
            display: inline-block;
        }

        .page-title:after {
            content: '';
            display: block;
            width: 40%;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
            margin-top: 5px;
        }

        /* Cards */
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
        }

        /* Summary Box */
        .summary-box {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .summary-box:before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(30deg);
            pointer-events: none;
        }

        .summary-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .summary-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .summary-period {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        /* Filter Form */
        .filter-form {
            background-color: #ffffff;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .filter-form .form-label {
            font-weight: 500;
            color: var(--dark-color);
        }

        .filter-form .form-control,
        .filter-form .form-select {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .filter-form .form-control:focus,
        .filter-form .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        /* Table Styling */
        .table-container {
            background: #fff;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eaeaea;
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0;
        }

        .table {
            width: 100%;
            margin-bottom: 0;
        }

        .table > :not(caption) > * > * {
            padding: 1rem;
        }

        .table > thead {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .table > thead > tr > th {
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
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

        .table tfoot tr {
            font-weight: 700;
            background-color: rgba(67, 97, 238, 0.05);
        }

        /* Buttons */
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: #fff;
        }

        .btn-success:hover {
            background-color: #26C6DA;
            border-color: #26C6DA;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(77, 208, 225, 0.15);
        }

        .btn-info {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
        }

        .btn-info:hover {
            background-color: #00B0FF;
            border-color: #00B0FF;
        }

        .btn-export {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            border: none;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 176, 155, 0.2);
            color: white;
        }

        .btn-back {
            background-color: #fff;
            color: var(--dark-color);
            border: 1px solid #e0e0e0;
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-back:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Badge */
        .badge {
            padding: 0.35rem 0.75rem;
            font-weight: 500;
            border-radius: 30px;
        }

        /* Animation */
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

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 2rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .empty-state p {
            color: #888;
            max-width: 300px;
            margin: 0 auto;
        }

        /* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-back {
        margin-top: 1rem;
    }

    .table-container {
        padding: 1rem;
        overflow-x: auto;
    }

    .table {
        display: block;
        white-space: nowrap;
    }

    .table-header {
        flex-direction: column;
        gap: 1rem;
    }

    .actions-container {
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    .btn-export, .btn-success {
        width: 100%;
        justify-content: center;
    }

    .summary-box {
        padding: 1.5rem;
    }

    .summary-value {
        font-size: 2rem;
    }

    .filter-form .row > div {
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .container {
        padding: 0 0.75rem;
    }

    .page-title {
        font-size: 1.5rem;
    }

    .summary-title {
        font-size: 0.9rem;
    }

    .summary-value {
        font-size: 1.75rem;
    }

    .card-header {
        padding: 1rem;
    }

    .table > :not(caption) > * > * {
        padding: 0.75rem;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <div class="dashboard-header fade-in">
            <h2 class="page-title">Rekap Pengeluaran</h2>
            <a href="<?php echo ($_SESSION['role'] == 'kepsek') ? 'dashboard_kepsek.php' : 'dashboard_bendahara.php'; ?>" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        
        <!-- Filter Form -->
        <div class="filter-form fade-in delay-1">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select class="form-select" id="tahun" name="tahun">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $tahun == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select class="form-select" id="bulan" name="bulan">
                        <option value="">Semua Bulan</option>
                        <option value="1" <?php echo $bulan == '1' ? 'selected' : ''; ?>>Januari</option>
                        <option value="2" <?php echo $bulan == '2' ? 'selected' : ''; ?>>Februari</option>
                        <option value="3" <?php echo $bulan == '3' ? 'selected' : ''; ?>>Maret</option>
                        <option value="4" <?php echo $bulan == '4' ? 'selected' : ''; ?>>April</option>
                        <option value="5" <?php echo $bulan == '5' ? 'selected' : ''; ?>>Mei</option>
                        <option value="6" <?php echo $bulan == '6' ? 'selected' : ''; ?>>Juni</option>
                        <option value="7" <?php echo $bulan == '7' ? 'selected' : ''; ?>>Juli</option>
                        <option value="8" <?php echo $bulan == '8' ? 'selected' : ''; ?>>Agustus</option>
                        <option value="9" <?php echo $bulan == '9' ? 'selected' : ''; ?>>September</option>
                        <option value="10" <?php echo $bulan == '10' ? 'selected' : ''; ?>>Oktober</option>
                        <option value="11" <?php echo $bulan == '11' ? 'selected' : ''; ?>>November</option>
                        <option value="12" <?php echo $bulan == '12' ? 'selected' : ''; ?>>Desember</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Cari Keterangan</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="Ketik keterangan...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Box -->
        <div class="row fade-in delay-2">
            <div class="col-md-12">
                <div class="summary-box">
                    <div class="summary-title">Total Pengeluaran</div>
                    <div class="summary-value">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></div>
                    <div class="summary-period">
                        <?php 
                        $period_text = "";
                        if (!empty($tahun)) {
                            $period_text .= "Tahun $tahun";
                        }
                        if (!empty($bulan)) {
                            $period_text .= " Bulan " . getNamaBulan($bulan);
                        }
                        if (!empty($period_text)) {
                            echo "<span class='badge bg-light text-dark'>Periode: $period_text</span>";
                        } else {
                            echo "<span class='badge bg-light text-dark'>Semua Periode</span>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container fade-in delay-3">
            <div class="table-header">
                <h4 class="table-title">Daftar Pengeluaran</h4>
                <div class="actions-container">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?tahun=<?php echo $tahun; ?>&bulan=<?php echo $bulan; ?>&search=<?php echo $search; ?>&export=pdf" class="btn-export">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="tambah_pengeluaran.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tambah Pengeluaran
                    </a>
                </div>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="40%">Keterangan</th>
                                <th width="20%">Jumlah</th>
                                <th width="20%">Tanggal</th>
                                <th width="15%">Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = $result->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><?php echo $row['keterangan']; ?></td>
                                    <td class="text-end">Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                    <td class="text-center"><?php echo date('d-m-Y', strtotime($row['tanggal_pengeluaran'])); ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['bukti_pengeluaran'])): ?>
                                            <a href="<?php echo $row['bukti_pengeluaran']; ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Ada</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end">Total</td>
                                <td class="text-end">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <h3>Tidak ada data pengeluaran</h3>
                    <p>Tidak ada data pengeluaran yang ditemukan untuk filter yang dipilih.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation classes to elements on page load
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.fade-in');
            animatedElements.forEach(element => {
                element.style.opacity = '1';
            });
        });
    </script>

    <?php include('footer.php'); ?>
</body>
</html>