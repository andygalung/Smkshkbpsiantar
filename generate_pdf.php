<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'administrasi') {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';

// Require the TCPDF library
require_once('vendor/tecnickcom/tcpdf/tcpdf.php ');

// Get parameters from URL
$id_pembayaran = $_GET['id'];
$kelas = $_GET['kelas'];

// Determine table based on class
$table = "";
switch ($kelas) {
    case "X":
        $table = "pembayaran_x";
        break;
    case "XI":
        $table = "pembayaran_xi";
        break;
    case "XII":
        $table = "pembayaran_xii";
        break;
    default:
        echo "<script>alert('Kelas tidak valid!'); window.location='dashboard_administrasi.php';</script>";
        exit();
}

// Get payment data from database
$query = "SELECT * FROM $table WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pembayaran);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Format date in Indonesian
$tanggal_pembayaran = date("d F Y", strtotime($data['tanggal_pembayaran']));
$bulan_array = array(
    'January' => 'Januari',
    'February' => 'Februari',
    'March' => 'Maret',
    'April' => 'April',
    'May' => 'Mei',
    'June' => 'Juni',
    'July' => 'Juli',
    'August' => 'Agustus',
    'September' => 'September',
    'October' => 'Oktober',
    'November' => 'November',
    'December' => 'Desember'
);
foreach ($bulan_array as $english => $indonesian) {
    $tanggal_pembayaran = str_replace($english, $indonesian, $tanggal_pembayaran);
}

// Generate unique receipt number
$nomor_struk = "SMK/".date("Y")."/".str_pad($id_pembayaran, 5, "0", STR_PAD_LEFT);

// Create new PDF document
class MYPDF extends TCPDF {
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Halaman '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Create new PDF document
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('SMK HKBP Siantar');
$pdf->SetAuthor('Administrasi SMK HKBP Siantar');
$pdf->SetTitle('Struk Pembayaran - ' . $data['nama_siswa']);
$pdf->SetSubject('Bukti Pembayaran Uang Sekolah');

// Set default header data
$pdf->SetHeaderData('', 0, '', '', array(0,0,0), array(255,255,255));

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set some additional styling
$pdf->SetFont('helvetica', '', 10);

// School header
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'SMK HKBP Siantar', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Jl. Contoh No. 123, Siantar, Sumatera Utara', 0, 1, 'C');
$pdf->Cell(0, 5, 'Telp: (061) 123-456 | Email: info@smkhkbpsiantar.sch.id', 0, 1, 'C');

// Line separator
$pdf->Line(15, $pdf->GetY() + 5, 195, $pdf->GetY() + 5);
$pdf->Ln(10);

// Receipt number
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'No. Struk: ' . $nomor_struk, 0, 1, 'R');

// Receipt title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'BUKTI PEMBAYARAN UANG SEKOLAH', 0, 1, 'C');
$pdf->Ln(5);

// Student details
$pdf->SetFont('helvetica', '', 11);
$pdf->SetFillColor(245, 245, 245);

// Create details table
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(60, 10, 'Nama Siswa', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(120, 10, $data['nama_siswa'], 1, 1, 'L');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(60, 10, 'Kelas / Jurusan', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(120, 10, $data['kelas'] . ' - ' . $data['jurusan'], 1, 1, 'L');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(60, 10, 'Tanggal Pembayaran', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(120, 10, $tanggal_pembayaran, 1, 1, 'L');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(60, 10, 'Metode Pembayaran', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(120, 10, 'Tunai', 1, 1, 'L');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(60, 10, 'Jumlah Pembayaran', 1, 0, 'L', true);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(120, 10, 'Rp ' . number_format($data['jumlah_bayar'], 0, ',', '.') . ',-', 1, 1, 'L');

$pdf->Ln(10);

// Add watermark text
$pdf->SetFont('helvetica', 'B', 60);
$pdf->SetTextColor(230, 230, 230);
// Get the current page dimensions
$width = $pdf->getPageWidth();
$height = $pdf->getPageHeight();
// Calculate center position and set the text with rotation
$pdf->StartTransform();
$pdf->Rotate(45, $width/2, $height/2);
$pdf->Text($width/2 - 40, $height/2, 'LUNAS');
$pdf->StopTransform();
$pdf->SetTextColor(0, 0, 0);

// Add signature section
$pdf->SetFont('helvetica', '', 10);
$pdf->Ln(20);
$pdf->Cell(90, 5, 'Penerima', 0, 0, 'C');
$pdf->Cell(90, 5, 'Bendahara Sekolah', 0, 1, 'C');

$pdf->Ln(25);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 5, $data['petugas'], 'T', 0, 'C');
$pdf->Cell(90, 5, 'Bendahara SMK HKBP Siantar', 'T', 1, 'C');

// Add circular stamp (simpler version without dashed lines)
$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(0, 0, 0);
// Draw circle for stamp
$pdf->Circle(145, 165, 15);
// Add text inside stamp
$pdf->SetFont('helvetica', 'B', 6);
$pdf->SetXY(135, 160);
$pdf->MultiCell(20, 3, "Pembayaran\nSah\nSMK HKBP", 0, 'C');

// Output PDF
$pdf->Output('Struk_Pembayaran_' . $data['nama_siswa'] . '.pdf', 'D');
?>