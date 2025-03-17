<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'administrasi') {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';

$id_pembayaran = $_GET['id'];
$kelas = $_GET['kelas'];

// Menentukan tabel berdasarkan kelas
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

// Mengambil data pembayaran dari database
$query = "SELECT * FROM $table WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pembayaran);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Format tanggal Indonesia
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

// Generate nomor struk unik
$nomor_struk = "SMK/".date("Y")."/".str_pad($id_pembayaran, 5, "0", STR_PAD_LEFT);

// Generate PDF jika tombol ditekan
if (isset($_POST['export_pdf'])) {
    // Redirect ke halaman khusus generate PDF
    header("Location: generate_pdf.php?id=$id_pembayaran&kelas=$kelas");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - SMK HKBP Siantar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .receipt-header {
            background-color: #f0f0f0;
            color: #333;
            padding: 30px;
            position: relative;
            border-bottom: 2px solid #ddd;
        }
        
        .school-logo {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo-circle {
            width: 70px;
            height: 70px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
        
        .header-text {
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .header-text h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header-text p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        
        .pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.1;
        }
        
        .receipt-body {
            padding: 30px;
        }
        
        .receipt-title {
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }
        
        .receipt-title h2 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            color: #333;
            display: inline-block;
            padding: 0 15px;
            background: #fff;
            position: relative;
            z-index: 1;
        }
        
        .receipt-title:after {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
            z-index: 0;
        }
        
        .receipt-no {
            text-align: right;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .receipt-no strong {
            color: #333;
            font-weight: 600;
        }
        
        .receipt-details {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .receipt-details th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: left;
            padding: 12px 15px;
            color: #333;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
        }
        
        .receipt-details td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            color: #444;
        }
        
        .receipt-details tr:last-child td,
        .receipt-details tr:last-child th {
            border-bottom: none;
        }
        
        .amount-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .receipt-footer {
            display: flex;
            justify-content: space-between;
            padding: 0 30px 30px;
        }
        
        .signature-section {
            text-align: center;
            width: 45%;
        }
        
        .signature-title {
            font-size: 14px;
            margin-bottom: 60px;
            color: #555;
        }
        
        .signature-name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        .stamp {
            position: relative;
            width: 120px;
            height: 120px;
            margin: -30px auto 0;
        }
        
        .stamp-inner {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px dashed #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-15deg);
            color: #333;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }
        
        .watermark {
            position: absolute;
            bottom: 20px;
            right: 30px;
            opacity: 0.05;
            font-size: 120px;
            transform: rotate(-45deg);
            font-weight: 700;
            color: #000;
            z-index: 0;
        }
        
        .btn-container {
            text-align: center;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-export {
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-export:hover {
            background-color: #1b5e20;
        }
        
        .btn-back {
            background-color: #757575;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            background-color: #616161;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt-container">
            <!-- Header -->
            <div class="receipt-header">
                <div class="pattern"></div>
                <div class="school-logo">
                    <div class="logo-circle">
                        <i class="fas fa-school fa-2x" style="color: #333;"></i>
                    </div>
                </div>
                <div class="header-text">
                    <h1>SMK HKBP Siantar</h1>
                    <p>Jl. Contoh No. 123, Siantar, Sumatera Utara</p>
                    <p>Telp: (061) 123-456 | Email: info@smkhkbpsiantar.sch.id</p>
                </div>
            </div>
            
            <!-- Body -->
            <div class="receipt-body">
                <div class="receipt-no">
                    No. Struk: <strong><?php echo $nomor_struk; ?></strong>
                </div>
                
                <div class="receipt-title">
                    <h2>BUKTI PEMBAYARAN UANG SEKOLAH</h2>
                </div>
                
                <div class="receipt-details">
                    <table>
                        <tr>
                            <th width="40%">Nama Siswa</th>
                            <td width="60%"><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                        </tr>
                        <tr>
                            <th>Kelas / Jurusan</th>
                            <td><?php echo htmlspecialchars($data['kelas']); ?> - <?php echo htmlspecialchars($data['jurusan']); ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td><?php echo $tanggal_pembayaran; ?></td>
                        </tr>
                        <tr>
                            <th>Metode Pembayaran</th>
                            <td>Tunai</td>
                        </tr>
                        <tr class="amount-row">
                            <th>Jumlah Pembayaran</th>
                            <td>Rp <?php echo number_format($data['jumlah_bayar'], 0, ',', '.'); ?>,-</td>
                        </tr>
                    </table>
                </div>
                
                <div class="watermark">LUNAS</div>
            </div>
            
            <!-- Footer with signatures and stamp -->
            <div class="receipt-footer">
                <div class="signature-section">
                    <div class="signature-title">Penerima</div>
                    <div class="signature-name"><?php echo htmlspecialchars($data['petugas']); ?></div>
                </div>
                
                <div class="signature-section">
                    <div class="stamp">
                        <div class="stamp-inner">
                            Pembayaran<br>Sah<br>SMK HKBP
                        </div>
                    </div>
                    <div class="signature-name">Bendahara Sekolah</div>
                </div>
            </div>
        </div>
        
        <!-- Buttons -->
        <div class="btn-container">
            <form method="post">
                <button type="submit" name="export_pdf" class="btn btn-export">
                    <i class="fas fa-file-pdf"></i> Ekspor ke PDF
                </button>
            </form>
            <a href="dashboard_administrasi.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</body>
</html>