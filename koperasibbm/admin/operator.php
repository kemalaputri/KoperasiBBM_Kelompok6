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
        $msg = "<div class='alert alert-success'>Operator ditambahkan! Kata sandi awal: operator123</div>";
    } else { $msg = "<div class='alert alert-danger'>Nama pengguna sudah dipakai!</div>"; }
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
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user=? AND role='operator'");
    $stmt->bind_param("i", $id);

    if($stmt->execute() && $stmt->affected_rows > 0) {
        $msg = "<div class='alert alert-success'>Operator berhasil dihapus dari database!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Operator gagal dihapus atau data tidak ditemukan.</div>";
    }
}

if(isset($_POST['reset_pass'])) {
    $id = (int)$_POST['id_user'];
    $new_pass = $_POST['new_pass'];
    if(strlen($new_pass) < 6) { $msg = "<div class='alert alert-danger'>Kata sandi minimal 6 karakter!</div>"; }
    else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id_user=? AND role='operator'");
        $stmt->bind_param("si", $hash, $id);

        if($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt = $conn->prepare("UPDATE password_request SET status='Approved' WHERE id_user=? AND status='Pending'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $msg = "<div class='alert alert-success'>Kata sandi operator berhasil diubah!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Kata sandi operator gagal diubah atau operator tidak ditemukan.</div>";
        }
    }
}

if(isset($_GET['approve_req'])) {
    $id_req = (int)$_GET['approve_req'];
    $stmt = $conn->prepare("SELECT pr.*, u.nama_lengkap FROM password_request pr JOIN users u ON pr.id_user=u.id_user WHERE pr.id_request=? AND pr.status='Pending' AND u.role='operator'");
    $stmt->bind_param("i", $id_req);
    $stmt->execute();
    $req_data = $stmt->get_result()->fetch_assoc();

    if($req_data && !empty($req_data['new_password'])) {
        $new_hash = $req_data['new_password'];
        $id_user = (int)$req_data['id_user'];

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id_user=? AND role='operator'");
            $stmt->bind_param("si", $new_hash, $id_user);
            $stmt->execute();

            if($stmt->affected_rows <= 0) {
                throw new Exception("Kata sandi operator gagal diperbarui.");
            }

            $stmt = $conn->prepare("UPDATE password_request SET status='Approved' WHERE id_request=? AND status='Pending'");
            $stmt->bind_param("i", $id_req);
            $stmt->execute();

            if($stmt->affected_rows <= 0) {
                throw new Exception("Status pengajuan gagal diperbarui.");
            }

            $conn->commit();
            $msg = "<div class='alert alert-success'>Pengajuan disetujui dan kata sandi diperbarui!</div>";
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "<div class='alert alert-danger'>Gagal menyetujui pengajuan. Kata sandi operator belum berubah.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Pengajuan tidak valid, sudah diproses, atau tidak memiliki kata sandi baru.</div>";
    }
}
if(isset($_GET['reject_req'])) {
    $id_req = (int)$_GET['reject_req'];
    $stmt = $conn->prepare("UPDATE password_request SET status='Rejected' WHERE id_request=? AND status='Pending'");
    $stmt->bind_param("i", $id_req);
    $stmt->execute();
    $msg = "<div class='alert alert-danger'>Pengajuan ditolak.</div>";
}

 $operators = mysqli_query($conn, "SELECT * FROM users WHERE role='operator'");
 $requests = mysqli_query($conn, "SELECT pr.*, u.nama_lengkap FROM password_request pr JOIN users u ON pr.id_user=u.id_user WHERE pr.status='Pending' AND pr.new_password IS NOT NULL AND pr.new_password <> '' ORDER BY pr.created_at DESC");
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
                    <form method="POST" action="<?php echo base_url(); ?>/admin/operator.php">
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required></div>
                        <div class="form-group"><label>Nama Pengguna</label><input type="text" name="username" required></div>
                        <div class="form-group"><label>No Telepon</label><input type="text" name="no_telepon"></div>
                        <button type="submit" name="tambah_op" class="btn btn-primary">Tambah</button>
                    </form>
                </div>
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Pengajuan Kata Sandi</h3>
                    <ul style="list-style:none; padding:0;">
                    <?php while($r = mysqli_fetch_assoc($requests)): ?>
                        <li style="border-bottom:1px solid var(--border-color); padding:10px 0;">
                            <strong><?php echo htmlspecialchars($r['nama_lengkap'] ?? ''); ?></strong> mengajukan perubahan kata sandi.
                            <div style="margin-top:8px;">
                                <a href="<?php echo base_url(); ?>/admin/operator.php?approve_req=<?php echo $r['id_request']; ?>" class="btn btn-success btn-sm">Setujui</a>
                                <a href="<?php echo base_url(); ?>/admin/operator.php?reject_req=<?php echo $r['id_request']; ?>" class="btn btn-danger btn-sm">Tolak</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($requests)==0) echo "<li style='color:var(--text-gray);'>Tidak ada pengajuan menunggu persetujuan.</li>"; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <h3 style="margin-bottom:15px;">Daftar Operator</h3>
                <table>
                    <thead><tr><th>Nama</th><th>Nama Pengguna</th><th>Telepon</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php while($o = mysqli_fetch_assoc($operators)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($o['nama_lengkap'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($o['username'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($o['no_telepon'] ?? '-'); ?></td>
                        <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                        <td>
                            <form method="POST" action="<?php echo base_url(); ?>/admin/operator.php" style="display:inline-flex; gap:5px; align-items:center;">
                                <input type="hidden" name="id_user" value="<?php echo $o['id_user']; ?>">
                                <input type="password" name="new_pass" placeholder="Kata sandi baru" required style="padding:5px 8px; font-size:0.8rem;">
                                <button type="submit" name="reset_pass" class="btn btn-warning btn-sm">Ubah</button>
                            </form>
                            <a href="<?php echo base_url(); ?>/admin/operator.php?delete_op=<?php echo $o['id_user']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus operator ini?')">Hapus</a>
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
