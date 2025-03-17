<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'bendahara') {
    header("Location: index.php");
    exit();
}
include 'koneksi.php';
include 'header.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keterangan = $_POST['keterangan'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    
    // Handle file upload if included
    $bukti_pengeluaran = "";
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $target_dir = "uploads/bukti_pengeluaran/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
            $bukti_pengeluaran = $target_file;
        } else {
            $error_message = "Gagal mengunggah file bukti pengeluaran.";
        }
    }
    
    // Insert data into the database
    $sql = "INSERT INTO pengeluaran (keterangan, jumlah, tanggal_pengeluaran, bukti_pengeluaran) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdss", $keterangan, $jumlah, $tanggal, $bukti_pengeluaran);
    
    if ($stmt->execute()) {
        $success_message = "Pengeluaran berhasil ditambahkan.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

// Get recent expenses for the table
$recent_expenses = $conn->query("SELECT * FROM pengeluaran ORDER BY tanggal_pengeluaran DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengeluaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --light-bg: #f8f9fa;
            --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
            --border-radius: 12px;
            --transition-speed: 0.3s;
        }
        
        body {
            background-color: #f0f2f5;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            margin-bottom: 2rem;
            background-color: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
            display: flex;
            align-items: center;
        }
        
        .card-header i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 1.75rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e1e5eb;
            font-size: 0.95rem;
            transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all var(--transition-speed);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-info {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #3aafda;
            border-color: #3aafda;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-top: 1.5rem;
        }
        
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e9ecef;
            font-size: 0.95rem;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table tr:hover {
            background-color: #f1f4f9;
        }
        
        .badge {
            padding: 0.5rem 0.85rem;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.15);
            color: var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload .file-select {
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            cursor: pointer;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            height: calc(3rem + 2px);
            display: flex;
            align-items: center;
        }
        
        .file-upload .file-select .file-select-button {
            background: #f0f2f5;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin-right: 0.5rem;
            color: #555;
        }
        
        .file-upload input[type="file"] {
            display: none;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }
        
        .currency-input {
            position: relative;
        }
        
        .currency-input input {
            padding-left: 3rem !important;
        }
        
        .currency-input::before {
            content: "Rp";
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #555;
            font-weight: 500;
            z-index: 10;
        }
        
        .table-actions {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .table-actions a {
            color: var(--primary-color);
            transition: color var(--transition-speed);
        }
        
        .table-actions a:hover {
            color: var(--secondary-color);
        }
        
        .file-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #f0f2f5;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #555;
            transition: background-color var(--transition-speed);
        }
        
        .file-badge:hover {
            background-color: #e1e5eb;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #888;
            font-style: italic;
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            .container {
                padding: 0.75rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .table-container {
                padding: 1rem;
                overflow-x: auto;
            }
            
            .btn {
                padding: 0.5rem 1rem;
            }
            
            .table th, .table td {
                padding: 0.75rem;
            }
            
            .back-btn {
                position: static;
                margin-bottom: 1rem;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <div class="back-btn no-print animate-fade-in">
        <a href="<?php echo ($_SESSION['role'] == 'kepsek') ? 'dashboard_kepsek.php' : 'dashboard_bendahara.php'; ?>" class="btn btn-primary btn-icon">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>
    
    <div class="container animate-fade-in">
        <h2 class="page-title"><i class="fas fa-money-bill-wave me-2"></i>Manajemen Pengeluaran</h2>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle"></i>
                        <h5 class="mb-0">Tambah Pengeluaran Baru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="keterangan" class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>Keterangan Pengeluaran
                                    </label>
                                    <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Masukkan keterangan pengeluaran" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                <label for="jumlah" class="form-label">
                                    <i class="fas fa-money-bill me-1"></i>Jumlah
                                </label>
                                <div class="currency-input">
                                    <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" step="any" placeholder="Masukkan jumlah" required>
                                </div>
                            </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Tanggal Pengeluaran
                                    </label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="bukti" class="form-label">
                                        <i class="fas fa-file-invoice me-1"></i>Bukti Pengeluaran
                                    </label>
                                    <div class="file-upload">
                                        <div class="file-select">
                                            <div class="file-select-button" id="fileName">Pilih File</div>
                                            <div class="file-select-name" id="noFile">Belum ada file yang dipilih</div>
                                            <input type="file" class="form-control" id="bukti" name="bukti" accept="image/*,.pdf">
                                        </div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">Unggah file gambar atau PDF bukti pengeluaran (opsional)</small>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="reset" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Pengeluaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Pengeluaran Terbaru</h4>
                <a href="rekap_pengeluaran.php" class="btn btn-info btn-sm">
                    <i class="fas fa-list me-1"></i>Lihat Semua
                </a>
            </div>
            
            <?php if ($recent_expenses->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40%">Keterangan</th>
                                <th width="20%">Jumlah</th>
                                <th width="20%">Tanggal</th>
                                <th width="20%">Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_expenses->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['keterangan']; ?></td>
                                    <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengeluaran'])); ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['bukti_pengeluaran'])): ?>
                                            <a href="<?php echo $row['bukti_pengeluaran']; ?>" target="_blank" class="file-badge">
                                                <i class="fas fa-file-alt"></i> Lihat Bukti
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary">Tidak ada bukti</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-info-circle me-2"></i>Belum ada data pengeluaran.
                </div>
            <?php endif; ?>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="rekap_pengeluaran.php" class="btn btn-info">
                    <i class="fas fa-chart-bar me-1"></i>Lihat Laporan Lengkap
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Update file input display
        document.getElementById('bukti').addEventListener('change', function() {
            const fileName = this.value.split('\\').pop();
            document.getElementById('noFile').textContent = fileName ? fileName : 'Belum ada file yang dipilih';
        });

        // Add this to your script section at the bottom of the page
        document.querySelector('.file-select').addEventListener('click', function() {
            document.getElementById('bukti').click();
        });
    </script>
    
    <?php include('footer.php'); ?>
</body>
</html>