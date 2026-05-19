<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';
 $user_id = $_SESSION['id_user'];
 $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user=$user_id"));

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pass = $_POST['password'];
    $konf = $_POST['konfirmasi_password'];
    if(strlen($pass) < 6) { $msg = "<div class='alert alert-danger'>Minimal 6 karakter!</div>"; }
    elseif($pass != $konf) { $msg = "<div class='alert alert-danger'>Kata sandi dan konfirmasi tidak sama!</div>"; }
    else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO password_request (id_user, new_password, status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param("is", $user_id, $hash);
        $stmt->execute();
        $msg = "<div class='alert alert-success'>Pengajuan perubahan kata sandi telah dikirim ke admin. Silakan tunggu persetujuan.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Profil Operator</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Profil</h2></div>
        <div class="content-wrapper">
            <p style="margin-bottom:20px; color:var(--text-gray);">Informasi akun Anda</p>
            <?php echo $msg; ?>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Informasi Akun</h3>
                    <table>
                        <tr><td style="width:130px; font-weight:600;">Nama Lengkap</td><td>: <?php echo htmlspecialchars($u['nama_lengkap'] ?? ''); ?></td></tr>
                        <tr><td style="font-weight:600;">Nama Pengguna</td><td>: <?php echo htmlspecialchars($u['username'] ?? ''); ?></td></tr>
                        <tr><td style="font-weight:600;">Nomor Telepon</td><td>: <?php echo htmlspecialchars($u['no_telepon'] ?? '-'); ?></td></tr>
                        <tr><td style="font-weight:600;">Role</td><td>: <span class="badge badge-warning">Operator</span></td></tr>
                    </table>
                </div>
                
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Pengajuan Perubahan Kata Sandi</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Kata Sandi Baru</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="konfirmasi_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajukan Perubahan Kata Sandi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
