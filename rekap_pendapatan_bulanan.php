<?php
session_start();
// Periksa apakah pengguna sudah login dan memiliki role 'kepsek' atau 'bendahara'
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'kepsek' && $_SESSION['role'] != 'bendahara')) {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';

// Ambil data pendapatan per bulan
$result = $conn->query("SELECT MONTH(tanggal_pembayaran) AS bulan, SUM(jumlah_bayar) AS total_pendapatan FROM (
    SELECT tanggal_pembayaran, jumlah_bayar FROM pembayaran_x 
    UNION ALL
    SELECT tanggal_pembayaran, jumlah_bayar FROM pembayaran_xi 
    UNION ALL
    SELECT tanggal_pembayaran, jumlah_bayar FROM pembayaran_xii
) AS pembayaran 
GROUP BY MONTH(tanggal_pembayaran) ORDER BY bulan ASC");

$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pendapatan Bulanan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Rekap Pendapatan Bulanan</h2>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Bulan</th>
                    <th>Total Pendapatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . $bulan_nama[$row['bulan']] . "</td>";
                    echo "<td>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
                    echo "<td><a href='rekap_pendapatan_detail.php?bulan=" . $row['bulan'] . "' class='btn btn-info'>Lihat Detail</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="<?php echo ($_SESSION['role'] == 'kepsek') ? 'dashboard_kepsek.php' : 'dashboard_bendahara.php'; ?>" class="btn btn-primary">Kembali</a>
    </div>
</body>
</html>
