<?php
session_start();
// Periksa apakah pengguna sudah login dan memiliki role 'kepsek' atau 'bendahara'
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'kepsek' && $_SESSION['role'] != 'bendahara')) {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';

if (!isset($_GET['bulan'])) {
    header("Location: rekap_pendapatan_bulanan.php");
    exit();
}

$bulan = intval($_GET['bulan']);
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Ambil data pembayaran berdasarkan bulan yang dipilih
$result = $conn->query("SELECT * FROM (
    SELECT tanggal_pembayaran, nama_siswa, jumlah_bayar FROM pembayaran_x 
    UNION ALL
    SELECT tanggal_pembayaran, nama_siswa, jumlah_bayar FROM pembayaran_xi 
    UNION ALL
    SELECT tanggal_pembayaran, nama_siswa, jumlah_bayar FROM pembayaran_xii
) AS pembayaran 
WHERE MONTH(tanggal_pembayaran) = $bulan ORDER BY tanggal_pembayaran DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pendapatan Bulan <?php echo $bulan_nama[$bulan]; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Rekap Pendapatan Bulan <?php echo $bulan_nama[$bulan]; ?></h2>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Jumlah Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . $row['nama_siswa'] . "</td>";
                    echo "<td>" . $row['tanggal_pembayaran'] . "</td>";
                    echo "<td>Rp " . number_format($row['jumlah_bayar'], 0, ',', '.') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="<?php echo ($_SESSION['role'] == 'kepsek') ? 'dashboard_kepsek.php' : 'dashboard_bendahara.php'; ?>" class="btn btn-primary">Kembali</a>
    </div>
</body>
</html>
