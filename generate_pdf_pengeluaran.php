<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'bendahara') {
    header("Location: index.php");
    exit();
}
ob_start();
require_once 'koneksi.php';
require_once 'vendor/autoload.php'; // Include TCPDF library

// Get parameters from URL
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filename = isset($_GET['filename']) ? $_GET['filename'] : 'Rekap_Pengeluaran.pdf';

ob_end_clean();

// Get nama bulan function
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

// Create new PDF document
class MYPDF extends TCPDF {
    public function Header() {
        // Set starting Y position lower
        $this->SetY(15); // Start header at 15mm from top (instead of default 10mm)
        
        // Logo - also positioned lower
        $image_file = 'img/logo-smkshkbpsiantar.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 15, 25, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // School name and address - now positioned lower
        $this->SetFont('helvetica', 'B', 14);
        $this->SetX($this->GetX() - 15); // Geser ke kiri 5 unit
        $this->Cell(0, 5, 'SMKS HKBP SIANTAR', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Jl. Patuan Anggi No.6, Proklamasi, Kec. Siantar Barat, Kota Pematang Siantar', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);
        $this->Cell(0, 5, 'Telp: (0622) 21899 - Email: sman2siantar@gmail.com', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Line - also positioned lower
        $this->Ln(6);
        $this->SetLineWidth(0.5);
        $this->Line(10, 35, 200, 35); // Y position increased from 30 to 35
        $this->SetLineWidth(0.2);
        $this->Line(10, 36, 200, 36); // Y position increased from 31 to 36
    }

    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Halaman '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        // Date printed
        $this->Cell(0, 10, 'Dicetak pada: '.date('d-m-Y H:i:s'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF instance
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistem Keuangan SMA Negeri 2 Pematangsiantar');
$pdf->SetTitle('Rekap Pengeluaran');
$pdf->SetSubject('Laporan Pengeluaran Keuangan Sekolah');
$pdf->SetKeywords('Pengeluaran, Laporan, PDF, Keuangan, Sekolah');

// Set default header data
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins - adjusted to accommodate the lower header
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Add report title with spacing
$pdf->Ln(5); // Add more space after the header
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'LAPORAN PENGELUARAN', 0, false, 'C', 0, '', 0, false, 'M', 'M');
$pdf->Ln(10); // Add space after the title

// Set font
$pdf->SetFont('helvetica', '', 11);

// Add period information
$period_text = "";
if (!empty($tahun)) {
    $period_text .= "Tahun $tahun";
}
if (!empty($bulan)) {
    $period_text .= " Bulan " . getNamaBulan($bulan);
}
if (!empty($period_text)) {
    $pdf->Cell(0, 10, 'Periode: '.$period_text, 0, false, 'C', 0, '', 0, false, 'M', 'M');
    $pdf->Ln(15);
} else {
    $pdf->Ln(10);
}

// If search is applied
if (!empty($search)) {
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Filter Keterangan: '.$search, 0, false, 'L', 0, '', 0, false, 'M', 'M');
    $pdf->Ln(10);
}

// Table header
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(10, 7, 'No', 1, 0, 'C', 0);
$pdf->Cell(90, 7, 'Keterangan', 1, 0, 'C', 0);
$pdf->Cell(40, 7, 'Jumlah', 1, 0, 'C', 0);
$pdf->Cell(40, 7, 'Tanggal', 1, 1, 'C', 0);

// Table data
$pdf->SetFont('helvetica', '', 10);
if ($result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(10, 7, $no++, 1, 0, 'C', 0);
        $pdf->Cell(90, 7, $row['keterangan'], 1, 0, 'L', 0);
        $pdf->Cell(40, 7, 'Rp ' . number_format($row['jumlah'], 0, ',', '.'), 1, 0, 'R', 0);
        $pdf->Cell(40, 7, date('d-m-Y', strtotime($row['tanggal_pengeluaran'])), 1, 1, 'C', 0);
    }
} else {
    $pdf->Cell(180, 7, 'Tidak ada data pengeluaran.', 1, 1, 'C', 0);
}

// Total
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(100, 7, 'Total', 1, 0, 'R', 0);
$pdf->Cell(80, 7, 'Rp ' . number_format($total_pengeluaran, 0, ',', '.'), 1, 1, 'R', 0);

// Add signature area
$pdf->Ln(15);
$pdf->SetFont('helvetica', '', 10);

// Get current date for signature
$current_date = date('d F Y');
$pdf->Cell(0, 7, 'Pematangsiantar, '.$current_date, 0, 1, 'R', 0);
$pdf->Ln(5);
$pdf->Cell(0, 7, 'Bendahara', 0, 1, 'R', 0);
$pdf->Ln(20);
$pdf->Cell(0, 7, '('.$_SESSION['username'].')', 0, 1, 'R', 0);

// Close and output PDF document
$pdf->Output($filename, 'I');
$conn->close();
?>