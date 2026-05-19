<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'user') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $id_user = $_SESSION['id_user'];
 $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user=$id_user"));
 $msg = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pass = $_POST['password'];
    $konf = $_POST['konfirmasi_password'];
    if(strlen($pass) < 6) { $msg = "<div class='alert alert-danger'>Minimal 6 karakter!</div>"; }
    elseif($pass != $konf) { $msg = "<div class='alert alert-danger'>Kata sandi tidak sama!</div>"; }
    else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id_user=$id_user");
        $msg = "<div class='alert alert-success'>Kata sandi berhasil diubah!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Profil</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="container" style="padding: 30px 0; max-width:600px;">
    <h2 class="section-title">Profil Saya</h2>
    <?php echo $msg; ?>
    <div class="card">
        <table>
            <tr><td width="130">Nama</td><td>: <?php echo htmlspecialchars($u['nama_lengkap']); ?></td></tr>
            <tr><td>Nama Pengguna</td><td>: <?php echo htmlspecialchars($u['username']); ?></td></tr>
            <tr><td>Telepon</td><td>: <?php echo htmlspecialchars($u['no_telepon']); ?></td></tr>
            <tr><td>Jenis Anggota</td><td>: <?php echo $u['jenis_anggota']; ?></td></tr>
        </table>
    </div>
    <div class="card" style="margin-top:20px;">
        <h3>Ubah Kata Sandi</h3>
        <form method="POST" style="margin-top:15px;">
            <div class="form-group"><label>Kata Sandi Baru</label><input type="password" name="password" required></div>
            <div class="form-group"><label>Konfirmasi Kata Sandi</label><input type="password" name="konfirmasi_password" required></div>
            <button type="submit" class="btn btn-primary">Simpan Kata Sandi</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</body>
</html>
