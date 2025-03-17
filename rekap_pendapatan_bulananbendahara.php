<?php
session_start();
// Periksa apakah pengguna sudah login dan memiliki role 'kepsek' atau 'bendahara'
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'kepsek' && $_SESSION['role'] != 'bendahara')) {
    header("Location: index.php");
    exit();
}


include 'koneksi.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

$tahun_sekarang = date('Y');

// Ambil data pendapatan per bulan
$result = $conn->query("
    SELECT 
        MONTH(tanggal_pembayaran) AS bulan, 
        SUM(jumlah_bayar) AS total_pendapatan 
    FROM (
        SELECT tanggal_pembayaran, jumlah_bayar FROM pembayaran_x 
        UNION ALL
        SELECT tanggal_pembayaran, jumlah_bayar FROM pembayaran_xi 
        UNION ALL
        SELECT tanggal_pembayaran, jumlah_bayar FROM pembayaran_xii
    ) AS pembayaran 
    GROUP BY MONTH(tanggal_pembayaran) 
    ORDER BY bulan ASC
");

$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Simpan data untuk chart dan total tahunan
$data_bulan = [];
$data_pendapatan = [];
$total_tahunan = 0;

if ($result->num_rows > 0) {
    $result_array = $result->fetch_all(MYSQLI_ASSOC);
    
    // Reset pointer result untuk digunakan di HTML
    $result->data_seek(0);
    
    foreach ($result_array as $row) {
        $data_bulan[] = $bulan_nama[$row['bulan']];
        $data_pendapatan[] = $row['total_pendapatan'];
        $total_tahunan += $row['total_pendapatan'];
    }
}

// Ambil data sekolah (misalnya dari database)
$nama_sekolah = "SMKS HKBP SIANTAR";
$alamat_sekolah = "Jl. Pendidikan No. 123, Kota Cerdas";
$telepon_sekolah = "(021) 1234-5678";
$email_sekolah = "info@smkn1teknologi.sch.id";
$website_sekolah = "www.smkn1teknologi.sch.id";
$logo_path = "img/logo_smkshkbpsiantar.png"; // Path logo sekolah

// Jika tombol "Ekspor PDF Tahunan" ditekan
if (isset($_GET['export']) && $_GET['export'] == "pdf") {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Sistem Informasi Sekolah');
    $pdf->SetAuthor('Bendahara Sekolah');
    $pdf->SetTitle("Rekap Pendapatan Tahunan $tahun_sekarang");
    
    // Remove header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Logo and header
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, 15, 15, 25);
    }
    
    // School information
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 7, $nama_sekolah, 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, $alamat_sekolah, 0, 1, 'C');
    $pdf->Cell(0, 5, "Telp: $telepon_sekolah | Email: $email_sekolah", 0, 1, 'C');
    $pdf->Cell(0, 5, "Website: $website_sekolah", 0, 1, 'C');
    
    // Line separator
    $pdf->Ln(2);
    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Ln(5);
    
    // Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, "REKAP PENDAPATAN TAHUNAN", 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 14);
    $pdf->Cell(0, 8, "Tahun $tahun_sekarang", 0, 1, 'C');
    $pdf->Ln(8);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(42, 62, 80);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(15, 10, 'No', 1, 0, 'C', true);
    $pdf->Cell(80, 10, 'Bulan', 1, 0, 'C', true);
    $pdf->Cell(80, 10, 'Total Pendapatan', 1, 1, 'C', true);
    
    // Reset text color
    $pdf->SetTextColor(0, 0, 0);
    
    // Table data
    $pdf->SetFont('helvetica', '', 10);
    $no = 1;
    
    if ($result->num_rows > 0) {
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            $pdf->Cell(15, 10, $no, 1, 0, 'C');
            $pdf->Cell(80, 10, $bulan_nama[$row['bulan']], 1, 0, 'L');
            $pdf->Cell(80, 10, 'Rp ' . number_format($row['total_pendapatan'], 0, ',', '.'), 1, 1, 'R');
            $no++;
        }
    }
    
    // Total row
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(95, 10, 'Total Pendapatan Tahunan', 1, 0, 'R', true);
    $pdf->Cell(80, 10, 'Rp ' . number_format($total_tahunan, 0, ',', '.'), 1, 1, 'R', true);
    
    // Add signature area
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 10, date('d') . ' ' . $bulan_nama[date('n')] . ' ' . date('Y'), 0, 1, 'R');
    $pdf->Cell(0, 10, 'Bendahara Sekolah,', 0, 1, 'R');
    $pdf->Ln(15);
    $pdf->Cell(0, 10, '(____________________)', 0, 1, 'R');
    
    // Footer
    $pdf->SetY(-35);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Ln(5);
    $pdf->Cell(0, 10, "Dokumen ini dicetak pada tanggal " . date('d-m-Y') . " melalui Sistem Informasi $nama_sekolah", 0, 0, 'C');
    
    // Output PDF
    $pdf->Output("Rekap_Pendapatan_Tahunan_$tahun_sekarang.pdf", 'D');
    exit();

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pendapatan Bulanan <?php echo $tahun_sekarang; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-gray: #ecf0f1;
        }
        
        body {
            background-color: #3498db;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .content-wrapper {
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .page-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
            font-weight: 600;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
        }
        
        .card-primary {
            border-top: 4px solid var(--primary-color);
        }
        
        .card-accent {
            border-top: 4px solid var(--accent-color);
        }
        
        .card-success {
            border-top: 4px solid var(--success-color);
        }
        
        .summary-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            align-self: center;
        }
        
        .summary-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        
        .summary-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e0e0e0;
        }
        
        .data-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            border: none;
            position: sticky;
            top: 0;
        }
        
        .data-table th:first-child {
            border-top-left-radius: 8px;
        }
        
        .data-table th:last-child {
            border-top-right-radius: 8px;
        }
        
        .data-table td {
            padding: 15px;
            vertical-align: middle;
            border-top: 1px solid #e0e0e0;
        }
        
        .data-table tr:nth-child(even) {
            background-color: var(--light-gray);
        }
        
        .data-table tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .data-table tr:last-child {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
        .btn-detail {
            background-color: var(--secondary-color);
            border: none;
            border-radius: 4px;
            color: white;
            padding: 8px 12px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .btn-detail:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: white;
        }
        
        .btn-export {
            background-color: var(--accent-color);
            border: none;
            padding: 10px 16px;
            transition: all 0.3s;
            color: white;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .btn-export:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: white;
        }
        
        .btn-back {
            background-color: var(--primary-color);
            border: none;
            padding: 10px 16px;
            transition: all 0.3s;
            color: white;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .btn-back:hover {
            background-color: #1a2530;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .chart-container {
            margin-top: 30px;
            margin-bottom: 30px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .chart-title {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .empty-data {
            text-align: center;
            padding: 40px 0;
            color: #777;
        }
        
        .badge-month {
            background-color: var(--secondary-color);
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
        }
        
        .total-amount {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container content-wrapper">
        <h2 class="text-center page-title">
            <i class="fas fa-chart-line me-2"></i>Rekap Pendapatan Bulanan <?php echo $tahun_sekarang; ?>
        </h2>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card card-primary">
                <i class="fas fa-calendar-alt summary-icon"></i>
                <div class="summary-value"><?php echo $result->num_rows; ?></div>
                <div class="summary-label">Bulan Tercatat</div>
            </div>
            
            <div class="summary-card card-accent">
                <i class="fas fa-money-bill-wave summary-icon"></i>
                <div class="summary-value">Rp <?php echo number_format($total_tahunan, 0, ',', '.'); ?></div>
                <div class="summary-label">Total Pendapatan</div>
            </div>
            
            <div class="summary-card card-success">
                <i class="fas fa-chart-bar summary-icon"></i>
                <div class="summary-value">
                    <?php
                        $rata_rata = ($result->num_rows > 0) ? number_format($total_tahunan / $result->num_rows, 0, ',', '.') : 0;
                        echo "Rp " . $rata_rata;
                    ?>
                </div>
                <div class="summary-label">Rata-rata per Bulan</div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="<?php echo ($_SESSION['role'] == 'kepsek') ? 'dashboard_kepsek.php' : 'dashboard_bendahara.php'; ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            
            <a href="?export=pdf" class="btn-export">
                <i class="fas fa-file-pdf"></i> Ekspor Pendapatan Tahunan PDF
            </a>
        </div>

        <!-- Chart Section -->
        <div class="chart-container">
            <h3 class="chart-title">Grafik Pendapatan Bulanan <?php echo $tahun_sekarang; ?></h3>
            <canvas id="incomeChart" height="100"></canvas>
        </div>
        
        <!-- Table Section -->
        <div class="table-responsive">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th width="30%">Bulan</th>
                        <th width="30%">Total Pendapatan</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='text-center'>" . $no++ . "</td>";
                            echo "<td><span class='badge-month'>" . $bulan_nama[$row['bulan']] . "</span></td>";
                            echo "<td class='total-amount'>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
                            echo "<td class='text-center'>
                                    <a href='rekap_pendapatan_detailben.php?bulan=" . $row['bulan'] . "' class='btn-detail'>
                                        <i class='fas fa-eye me-1'></i> Detail
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>Tidak ada data pendapatan</td></tr>";
                    }
                    
                    // Add total row
                    if ($result->num_rows > 0) {
                        echo "<tr>";
                        echo "<td colspan='2' class='text-end'>Total Pendapatan Tahunan</td>";
                        echo "<td class='total-amount'>Rp " . number_format($total_tahunan, 0, ',', '.') . "</td>";
                        echo "<td></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('incomeChart').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($data_bulan); ?>,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: <?php echo json_encode($data_pendapatan); ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var value = context.raw;
                                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>