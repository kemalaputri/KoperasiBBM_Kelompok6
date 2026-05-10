<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';
if(isset($_POST['tambah_op'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $telp = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $pass = password_hash('operator123', PASSWORD_DEFAULT);
    if(mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password, no_telepon, role) VALUES ('$nama', '$user', '$pass', '$telp', 'operator')")) {
        $msg = "<div class='alert alert-success'>Operator ditambahkan! (Default Pass: operator123)</div>";
    } else { $msg = "<div class='alert alert-danger'>Username sudah dipakai!</div>"; }
}

if(isset($_POST['edit_op'])) {
    $id = (int)$_POST['id_user'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $telp = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', no_telepon='$telp' WHERE id_user=$id");
    $msg = "<div class='alert alert-success'>Data operator diupdate!</div>";
}

if(isset($_GET['delete_op'])) {
    $id = (int)$_GET['delete_op'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user=$id AND role='operator'");
    $msg = "<div class='alert alert-success'>Operator dihapus!</div>";
}

if(isset($_POST['reset_pass'])) {
    $id = (int)$_POST['id_user'];
    $new_pass = $_POST['new_pass'];
    if(strlen($new_pass) < 6) { $msg = "<div class='alert alert-danger'>Password min 6 karakter!</div>"; }
    else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id_user=$id");
        mysqli_query($conn, "UPDATE password_request SET status='disetujui' WHERE id_user=$id AND status='pending'");
        $msg = "<div class='alert alert-success'>Password direset manual!</div>";
    }
}

if(isset($_GET['approve_req'])) {
    $id_req = (int)$_GET['approve_req'];
    $req_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM password_request WHERE id_request=$id_req"));
    if($req_data && !empty($req_data['new_password'])) {
        $new_hash = $req_data['new_password'];
        $id_user = $req_data['id_user'];
        mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id_user=$id_user");
        mysqli_query($conn, "UPDATE password_request SET status='disetujui' WHERE id_request=$id_req");
        $msg = "<div class='alert alert-success'>Request disetujui & password diperbarui!</div>";
    } else {
        mysqli_query($conn, "UPDATE password_request SET status='disetujui' WHERE id_request=$id_req");
        $msg = "<div class='alert alert-warning'>Request disetujui, tapi tidak ada password baru terdeteksi. Silakan reset manual.</div>";
    }
}
if(isset($_GET['reject_req'])) {
    $id_req = (int)$_GET['reject_req'];
    mysqli_query($conn, "UPDATE password_request SET status='ditolak' WHERE id_request=$id_req");
    $msg = "<div class='alert alert-danger'>Request ditolak.</div>";
}

 $operators = mysqli_query($conn, "SELECT * FROM users WHERE role='operator'");
 $requests = mysqli_query($conn, "SELECT pr.*, u.nama_lengkap FROM password_request pr JOIN users u ON pr.id_user=u.id_user WHERE pr.status='pending'");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Kelola Operator</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Kelola Operator</h2></div>
        <div class="content-wrapper">
            <?php echo $msg; ?>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Tambah Operator</h3>
                    <form method="POST">
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required></div>
                        <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                        <div class="form-group"><label>No Telepon</label><input type="text" name="no_telepon"></div>
                        <button type="submit" name="tambah_op" class="btn btn-primary">Tambah</button>
                    </form>
                </div>
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Request Password</h3>
                    <ul style="list-style:none; padding:0;">
                    <?php while($r = mysqli_fetch_assoc($requests)): ?>
                        <li style="border-bottom:1px solid var(--border-color); padding:10px 0;">
                            <strong><?php echo htmlspecialchars($r['nama_lengkap'] ?? ''); ?></strong> mengajukan perubahan password.
                            <div style="margin-top:8px;">
                                <a href="?approve_req=<?php echo $r['id_request']; ?>" class="btn btn-success btn-sm">Setujui</a>
                                <a href="?reject_req=<?php echo $r['id_request']; ?>" class="btn btn-danger btn-sm">Tolak</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($requests)==0) echo "<li style='color:var(--text-gray);'>Tidak ada request pending.</li>"; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <h3 style="margin-bottom:15px;">Daftar Operator</h3>
                <table>
                    <thead><tr><th>Nama</th><th>Username</th><th>Telepon</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php while($o = mysqli_fetch_assoc($operators)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($o['nama_lengkap'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($o['username'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($o['no_telepon'] ?? '-'); ?></td>
                        <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                        <td>
                            <form method="POST" style="display:inline-flex; gap:5px; align-items:center;">
                                <input type="hidden" name="id_user" value="<?php echo $o['id_user']; ?>">
                                <input type="password" name="new_pass" placeholder="Reset Pass Manual" required style="padding:5px 8px; font-size:0.8rem;">
                                <button type="submit" name="reset_pass" class="btn btn-warning btn-sm">Reset</button>
                            </form>
                            <a href="?delete_op=<?php echo $o['id_user']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus operator ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>