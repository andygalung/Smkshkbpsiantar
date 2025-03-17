<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'administrasi') {
    header("Location: index.php");
    exit();
}
include 'header.php';
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_siswa = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    $jumlah_bayar = $_POST['jumlah_bayar'];
    $tanggal_pembayaran = date("Y-m-d");
    $petugas = $_SESSION['username'];

    if ($kelas == "X") {
        $table = "pembayaran_x";
    } elseif ($kelas == "XI") {
        $table = "pembayaran_xi";
    } elseif ($kelas == "XII") {
        $table = "pembayaran_xii";
    } else {
        echo "<script>alert('Kelas tidak valid!'); window.location='input_pembayaran.php';</script>";
        exit();
    }

    $query = "INSERT INTO $table (nama_siswa, kelas, jurusan, jumlah_bayar, tanggal_pembayaran, petugas) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssiss", $nama_siswa, $kelas, $jurusan, $jumlah_bayar, $tanggal_pembayaran, $petugas);
    if ($stmt->execute()) {
        $id_pembayaran = $stmt->insert_id;
        echo "<script>window.location='cetak_struk.php?id=$id_pembayaran&kelas=$kelas';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan, coba lagi!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --light-color: #f3f4f6;
            --dark-color: #1f2937;
        }
        body {
            background-color: #f9fafb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0 !important;
        }
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .btn-secondary {
            background-color: #e5e7eb;
            border-color: #e5e7eb;
            color: var(--dark-color);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-secondary:hover {
            background-color: #d1d5db;
            border-color: #d1d5db;
        }
        .page-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0;
        }
        .input-group-text {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-right: none;
        }
        .input-icon {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
    </style>
</head>
<body>
   

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="page-title">
                            <i class="fas fa-file-invoice me-2"></i>
                            Input Pembayaran
                        </h4>
                        <a href="dashboard_administrasi.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nama Siswa
                                </label>
                                <input type="text" name="nama_siswa" class="form-control" placeholder="Masukkan nama lengkap siswa" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-layer-group me-1"></i>
                                        Kelas
                                    </label>
                                    <select name="kelas" class="form-select" required>
                                        <option value="" selected disabled>Pilih Kelas</option>
                                        <option value="X">X</option>
                                        <option value="XI">XI</option>
                                        <option value="XII">XII</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-bookmark me-1"></i>
                                        Jurusan
                                    </label>
                                    <input type="text" name="jurusan" class="form-control" placeholder="contoh: TKJ, TKR, RPL" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Jumlah Pembayaran
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text input-icon">Rp</span>
                                    <input type="number" name="jumlah_bayar" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i>
                                Simpan & Cetak Struk
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>