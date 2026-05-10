<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

 $error = "";
 $success = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];
    $telp = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $jenis = $_POST['jenis_anggota'];
    
    $nis = isset($_POST['nis']) ? mysqli_real_escape_string($conn, $_POST['nis']) : NULL;
    $nip = isset($_POST['nip']) ? mysqli_real_escape_string($conn, $_POST['nip']) : NULL;
    $bagian = isset($_POST['bagian_internal']) ? mysqli_real_escape_string($conn, $_POST['bagian_internal']) : NULL;

    if($password !== $konfirmasi) {
        $error = "Password dan konfirmasi tidak cocok!";
    } else {
        $check = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
        if(mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $hash_pass = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (nama_lengkap, username, password, no_telepon, role, jenis_anggota, nis, nip, bagian_internal) 
                    VALUES ('$nama', '$username', '$hash_pass', '$telp', 'user', '$jenis', '$nis', '$nip', '$bagian')";
            
            if(mysqli_query($conn, $query)) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan sistem.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Koperasi BBM</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Daftar Akun</h2>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="konfirmasi_password" required>
            </div>
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="no_telepon" required>
            </div>
            <div class="form-group">
                <label>Daftar Sebagai</label>
                <select name="jenis_anggota" id="daftar_sebagai" required>
                    <option value="">-- Pilih --</option>
                    <option value="Murid">Murid</option>
                    <option value="Guru">Guru</option>
                    <option value="Internal Sekolah">Internal Sekolah</option>
                </select>
            </div>
            
            <div class="form-group" id="field-nis" style="display:none;">
                <label>NIS</label>
                <input type="text" name="nis">
            </div>
            <div class="form-group" id="field-nip" style="display:none;">
                <label>NIP</label>
                <input type="text" name="nip">
            </div>
            <div class="form-group" id="field-bagian" style="display:none;">
                <label>Bagian Internal</label>
                <select name="bagian_internal">
                    <option value="kantin">Kantin</option>
                    <option value="satpam">Satpam</option>
                    <option value="staf sekolah">Staf Sekolah</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Daftar</button>
        </form>
        <div class="auth-link">
            Sudah punya akun? <a href="<?php echo base_url(); ?>/auth/login.php">Masuk</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</body>
</html>