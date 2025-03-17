<?php
session_start();
require 'koneksi.php';

require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';



// Periksa apakah pengguna sudah login dan memiliki role yang sesuai
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'kepsek' && $_SESSION['role'] != 'bendahara')) {
    header("Location: index.php");
    exit();
}

// Periksa apakah parameter bulan tersedia
if (!isset($_GET['bulan'])) {
    header("Location: rekap_pendapatan_bulananbendahara.php");
    exit();
}

$bulan = intval($_GET['bulan']);
$tahun = date('Y');
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Ambil data pembayaran berdasarkan bulan yang dipilih
$query = "
    SELECT tanggal_pembayaran, nama_siswa, jumlah_bayar FROM pembayaran_x WHERE MONTH(tanggal_pembayaran) = $bulan
    UNION ALL
    SELECT tanggal_pembayaran, nama_siswa, jumlah_bayar FROM pembayaran_xi WHERE MONTH(tanggal_pembayaran) = $bulan
    UNION ALL
    SELECT tanggal_pembayaran, nama_siswa, jumlah_bayar FROM pembayaran_xii WHERE MONTH(tanggal_pembayaran) = $bulan
    ORDER BY tanggal_pembayaran DESC
";

$result = $conn->query($query);

// Menghitung total pendapatan
$total_pendapatan = 0;
$data_pembayaran = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_pendapatan += $row['jumlah_bayar'];
        $data_pembayaran[] = $row;
    }
}

// Ambil data sekolah (misalnya dari database)
$nama_sekolah = "SMKS HKBP SIANTAR";
$alamat_sekolah = "Jl. Pendidikan No. 123, Kota Cerdas";
$telepon_sekolah = "(021) 1234-5678";
$email_sekolah = "info@smkn1teknologi.sch.id";
$website_sekolah = "www.smkn1teknologi.sch.id";
$logo_path = "img/logo_smkshkbpsiantar.png"; // Path logo sekolah

// Jika tombol "Ekspor PDF" ditekan
if (isset($_GET['export']) && $_GET['export'] == "pdf") {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Sistem Informasi Sekolah');
    $pdf->SetAuthor('Bendahara Sekolah');
    $pdf->SetTitle("Rekap Pendapatan Bulan {$bulan_nama[$bulan]} $tahun");
    
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
        $pdf->Image($logo_path, 15, 15, 25, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
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
    $pdf->Cell(0, 10, "REKAP PENDAPATAN", 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 14);
    $pdf->Cell(0, 8, "Bulan {$bulan_nama[$bulan]} $tahun", 0, 1, 'C');
    $pdf->Ln(8);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(42, 62, 80); // Warna header tabel (primary color)
    $pdf->SetTextColor(255, 255, 255); // Warna teks putih
    $pdf->Cell(15, 10, 'No', 1, 0, 'C', true);
    $pdf->Cell(75, 10, 'Nama Siswa', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Tanggal Pembayaran', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Jumlah Bayar', 1, 1, 'C', true);
    
    // Reset text color
    $pdf->SetTextColor(0, 0, 0);
    
    // Table data
    $pdf->SetFont('helvetica', '', 10);
    $no = 1;
    $fill = false;
    
    foreach ($data_pembayaran as $row) {
        $pdf->SetFillColor(245, 245, 245); // Light gray for alternating rows
        $pdf->Cell(15, 10, $no, 1, 0, 'C', $fill);
        $pdf->Cell(75, 10, $row['nama_siswa'], 1, 0, 'L', $fill);
        $pdf->Cell(45, 10, date('d-m-Y', strtotime($row['tanggal_pembayaran'])), 1, 0, 'C', $fill);
        $pdf->Cell(45, 10, 'Rp ' . number_format($row['jumlah_bayar'], 0, ',', '.'), 1, 1, 'R', $fill);
        $no++;
        $fill = !$fill; // Alternate row colors
    }
    
    // Total row
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(135, 10, 'Total Pendapatan', 1, 0, 'R', true);
    $pdf->Cell(45, 10, 'Rp ' . number_format($total_pendapatan, 0, ',', '.'), 1, 1, 'R', true);
    
    // Add signature area
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 10, date('d') . ' ' . $bulan_nama[date('n')] . ' ' . date('Y'), 0, 1, 'R');
    $pdf->Cell(0, 10, 'Bendahara Sekolah,', 0, 1, 'R');
    $pdf->Ln(15);
    $pdf->Cell(0, 10, '(____________________)', 0, 1, 'R');
    
    // Add footer
    $pdf->SetY(-35);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 0, '', 'T', 1); // Line separator
    $pdf->Ln(5);
    $pdf->Cell(0, 10, "Dokumen ini dicetak pada tanggal " . date('d-m-Y') . " melalui Sistem Informasi $nama_sekolah", 0, 0, 'C');
    
    // Output PDF
    $pdf->Output("Rekap_Pendapatan_Bulan_{$bulan_nama[$bulan]}_$tahun.pdf", 'D');
    exit();
}

// Reset result for HTML display
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pendapatan Bulan <?php echo $bulan_nama[$bulan]; ?> <?php echo $tahun; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --text-primary: #333333;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: #f0f2f5;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 70px; /* Space for footer */
            color: var(--text-primary);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header Styles */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-bg));
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }
        
        .main-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-left {
            display: flex;
            align-items: center;
        }
        
        .logo-container {
            margin-right: 20px;
        }
        
        .logo-img {
            width: 60px;
            height: auto;
        }
        
        .school-info h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .school-info p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info .btn {
            transition: all 0.3s;
        }
        
        .user-info .btn:hover {
            transform: translateY(-2px);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-role {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        /* Content Styles */
        .content-wrapper {
            flex: 1;
            position: relative;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            padding: 30px;
            margin: 30px auto;
            max-width: 1200px;
            width: 95%;
        }
        
        .page-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .page-title i {
            color: var(--secondary-color);
        }
        
        .info-card {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .info-card-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .info-card-content h4 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .info-card-content p {
            margin: 0;
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }
        
        
        .data-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .data-table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: var(--border-color);
            transition: all 0.2s;
        }
        
        .data-table tr:nth-child(even) {
            background-color: rgba(240, 240, 240, 0.5);
        }
        
        .data-table tr:hover {
            background-color: rgba(52, 152, 219, 0.08);
        }
        
        .total-row {
            background-color: rgba(44, 62, 80, 0.05) !important;
            font-weight: bold;
            border-top: 2px solid var(--secondary-color);
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn {
            border-radius: 6px;
            font-weight: 500;
            letter-spacing: 0.3px;
            padding: 8px 20px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .btn i {
            margin-right: 6px;
        }
        
        .btn-export {
            background-color: var(--accent-color);
            border: none;
            color: white;
        }
        
        .btn-export:hover {
            background-color: #c0392b;
        }
        
        .btn-back {
            background-color: var(--primary-color);
            border: none;
            color: white;
        }
        
        .btn-back:hover {
            background-color: #1a2530;
        }
        
        .empty-data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
            color: var(--text-secondary);
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 8px;
            border: 1px dashed var(--border-color);
        }
        
        .empty-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--secondary-color);
            opacity: 0.7;
        }
        
        .empty-data p {
            font-size: 1.1rem;
            max-width: 400px;
            text-align: center;
            margin-bottom: 0;
        }
        
        /* Animated Elements */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-header .container {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .header-left {
                margin-bottom: 15px;
                justify-content: center;
            }
            
            .info-card {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .footer-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
   

    <!-- Main Content -->
    <div class="container content-wrapper fade-in">
        <h2 class="page-title">
            <i class="fas fa-chart-line"></i>
            Rekap Pendapatan Bulan <?php echo $bulan_nama[$bulan]; ?> <?php echo $tahun; ?>
        </h2>
        
        <?php if ($result->num_rows > 0) : ?>
        <!-- Total Card -->
        <div class="info-card">
            <div class="info-card-content">
                <h4>Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h4>
                <p>Total Pendapatan Bulan <?php echo $bulan_nama[$bulan]; ?></p>
            </div>
            <div class="info-card-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="<?php echo ($_SESSION['role'] == 'kepsek') ? 'dashboard_kepsek.php' : 'dashboard_bendahara.php'; ?>" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            
            <a href="?bulan=<?php echo $bulan; ?>&export=pdf" class="btn btn-export">
                <i class="fas fa-file-pdf"></i> Ekspor PDF
            </a>
        </div>

        <div class="table-container">
            <?php if ($result->num_rows > 0) : ?>
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="40%">Nama Siswa</th>
                            <th class="text-center" width="25%">Tanggal Pembayaran</th>
                            <th class="text-end" width="30%">Jumlah Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $total = 0;
                        while ($row = $result->fetch_assoc()) {
                            $total += $row['jumlah_bayar'];
                            echo "<tr>";
                            echo "<td class='text-center'>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_siswa']) . "</td>";
                            echo "<td class='text-center'>" . date('d-m-Y', strtotime($row['tanggal_pembayaran'])) . "</td>";
                            echo "<td class='text-end'>Rp " . number_format($row['jumlah_bayar'], 0, ',', '.') . "</td>";
                            echo "</tr>";
                        }
                        ?>
                        <tr class="total-row">
                            <td colspan="3" class="text-end">Total Pendapatan</td>
                            <td class="text-end">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-data">
                    <i class="fas fa-info-circle"></i>
                    <p>Tidak ada data pendapatan pada bulan <?php echo $bulan_nama[$bulan]; ?> <?php echo $tahun; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include('footer.php'); ?>   

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation to total value
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in effect to elements if needed
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(el => {
                el.classList.add('show');
            });
        });
    </script>
</body>
</html>