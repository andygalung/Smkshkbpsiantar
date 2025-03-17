<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'kepsek') {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';
include 'header.php';

// Filter parameters
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// Build query with filters
$query = "SELECT * FROM pengeluaran WHERE 1=1";

if (!empty($bulan) && $bulan != 'all') {
    $query .= " AND MONTH(tanggal_pengeluaran) = '$bulan'";
}

if (!empty($tahun)) {
    $query .= " AND YEAR(tanggal_pengeluaran) = '$tahun'";
}

if (!empty($keyword)) {
    $query .= " AND (keterangan LIKE '%$keyword%' OR kategori LIKE '%$keyword%')";
}

// Add ordering
$query .= " ORDER BY tanggal_pengeluaran DESC";

// Execute the query
$result = $conn->query($query);

// Get total expenses for the selected period
$total_query = "SELECT SUM(jumlah) AS total FROM pengeluaran WHERE 1=1";

if (!empty($bulan) && $bulan != 'all') {
    $total_query .= " AND MONTH(tanggal_pengeluaran) = '$bulan'";
}

if (!empty($tahun)) {
    $total_query .= " AND YEAR(tanggal_pengeluaran) = '$tahun'";
}

$total_result = $conn->query($total_query);
$total_pengeluaran = $total_result->fetch_assoc()['total'] ?: 0;

// Get categories for filter dropdown
$categories_query = "SELECT DISTINCT kategori FROM pengeluaran ORDER BY kategori";
$categories_result = $conn->query($categories_query);

// Export to Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Set headers for Excel download
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Pengeluaran_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // Create the Excel output
    echo '<table border="1">';
    echo '<tr><th colspan="5">LAPORAN PENGELUARAN SEKOLAH</th></tr>';
    echo '<tr><th colspan="5">Periode: ' . ($bulan != 'all' ? date('F', mktime(0, 0, 0, $bulan, 1)) : 'Semua Bulan') . ' ' . $tahun . '</th></tr>';
    echo '<tr><th colspan="5">Total Pengeluaran: Rp ' . number_format($total_pengeluaran) . '</th></tr>';
    echo '<tr><th>No</th><th>Tanggal</th><th>Keterangan</th><th>Jumlah (Rp)</th></tr>';
    
    $no = 1;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($row['tanggal_pengeluaran'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['kategori']) . '</td>';
            echo '<td>' . htmlspecialchars($row['keterangan']) . '</td>';
            echo '<td>' . number_format($row['jumlah']) . '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
    exit();
}

// Get years for the filter dropdown
$years_query = "SELECT DISTINCT YEAR(tanggal_pengeluaran) AS year FROM pengeluaran ORDER BY year DESC";
$years_result = $conn->query($years_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengeluaran - Kepala Sekolah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
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

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
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

        .filter-form {
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .table-container {
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            overflow-x: auto;
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

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 20px;
        }

        .summary-box {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .summary-title {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .summary-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }

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
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">Laporan Pengeluaran</h1>
                <p class="text-muted">Manajemen dan pemantauan pengeluaran sekolah</p>
            </div>
            <div>
                <a href="dashboard_kepsek.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="summary-box">
                    <h5 class="summary-title">Total Pengeluaran (<?php echo ($bulan != 'all' ? date('F', mktime(0, 0, 0, $bulan, 1)) : 'Semua Bulan') . ' ' . $tahun; ?>)</h5>
                    <h2 class="summary-value">Rp <?php echo number_format($total_pengeluaran); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card filter-form">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan">
                                <option value="all" <?php echo $bulan == 'all' ? 'selected' : ''; ?>>Semua Bulan</option>
                                <?php
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
                                foreach ($nama_bulan as $value => $label) {
                                    echo '<option value="' . $value . '" ' . ($bulan == $value ? 'selected' : '') . '>' . $label . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun">
                                <?php
                                if ($years_result->num_rows > 0) {
                                    while ($year_row = $years_result->fetch_assoc()) {
                                        echo '<option value="' . $year_row['year'] . '" ' . ($tahun == $year_row['year'] ? 'selected' : '') . '>' . $year_row['year'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="' . date('Y') . '">' . date('Y') . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        
        
        <div class="row">
            <div class="col-md-12">
                <div class="table-container">
                    <table class="table table-hover">
                        <thead>
                        <tr>
    <th width="5%">No</th>
    <th width="15%">Tanggal</th>
    
    <th width="55%">Keterangan</th>
    <th width="10%">Jumlah (Rp)</th>
</tr>
</thead>
<tbody>
<?php
$no = 1;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengeluaran'])); ?></td>
            
            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
            <td class="text-end fw-bold"><?php echo number_format($row['jumlah']); ?></td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
        <td colspan="5" class="text-center py-4">
            <i class="fas fa-exclamation-circle text-muted me-2"></i> Tidak ada data pengeluaran ditemukan
        </td>
    </tr>
    <?php
}
?>
</tbody>
</table>
</div>
</div>
</div>

<div class="row mt-4">
<div class="col-md-12">

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Get expense by category data for chart
<?php
$category_chart_query = "SELECT kategori, SUM(jumlah) as total FROM pengeluaran WHERE 1=1 ";

if (!empty($bulan) && $bulan != 'all') {
    $category_chart_query .= " AND MONTH(tanggal_pengeluaran) = '$bulan'";
}

if (!empty($tahun)) {
    $category_chart_query .= " AND YEAR(tanggal_pengeluaran) = '$tahun'";
}

$category_chart_query .= " GROUP BY kategori ORDER BY total DESC";
$category_chart_result = $conn->query($category_chart_query);

$categories = [];
$amounts = [];

if ($category_chart_result->num_rows > 0) {
    while ($cat_row = $category_chart_result->fetch_assoc()) {
        $categories[] = $cat_row['kategori'];
        $amounts[] = $cat_row['total'];
    }
}
?>

// Setup expense category chart
const expenseCategoryCtx = document.getElementById('expenseCategoryChart').getContext('2d');
const expenseCategoryChart = new Chart(expenseCategoryCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            label: 'Total Pengeluaran',
            data: <?php echo json_encode($amounts); ?>,
            backgroundColor: [
                'rgba(75, 192, 192, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(201, 203, 207, 0.7)',
                'rgba(67, 97, 238, 0.7)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(201, 203, 207, 1)',
                'rgba(67, 97, 238, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
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
</script>
<?php include('footer.php'); ?>  
</body>
</html>