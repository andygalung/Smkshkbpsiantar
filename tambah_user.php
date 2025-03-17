<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi ke database



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash password sebelum disimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database
    $query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('User berhasil ditambahkan!'); window.location='tambah_user.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan user!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center">Tambah User Baru</h3>
        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-body">
                <form action="tambah_user.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="administrasi">Administrasi</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="kepsek">Kepsek</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Tambah User</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
