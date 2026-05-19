<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : '';
 $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

 $where = "role='user'";
if($filter) $where .= " AND jenis_anggota='$filter'";
if($search) $where .= " AND (nama_lengkap LIKE '%$search%' OR username LIKE '%$search%')";

 $anggota = mysqli_query($conn, "SELECT * FROM users WHERE $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Data Anggota</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Data Anggota</h2></div>
        <div class="content-wrapper">
            <div class="card">
                <form method="GET" style="display:flex; gap:10px; margin-bottom:15px;">
                    <input type="text" name="search" placeholder="Cari nama/nama pengguna..." value="<?php echo htmlspecialchars($search); ?>" style="flex:1; padding:8px; border:1px solid #ccc; border-radius:5px;">
                    <select name="filter" onchange="this.form.submit()" style="padding:8px; border:1px solid #ccc; border-radius:5px;">
                        <option value="">Semua Jenis</option>
                        <option value="Murid" <?php echo $filter=='Murid'?'selected':''; ?>>Murid</option>
                        <option value="Guru" <?php echo $filter=='Guru'?'selected':''; ?>>Guru</option>
                        <option value="Internal Sekolah" <?php echo $filter=='Internal Sekolah'?'selected':''; ?>>Internal</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Cari</button>
                </form>
                <table>
                    <thead><tr><th>Nama</th><th>Nama Pengguna</th><th>Jenis</th><th>NIS/NIP/Bagian</th><th>Telepon</th></tr></thead>
                    <tbody>
                    <?php while($a = mysqli_fetch_assoc($anggota)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($a['username']); ?></td>
                        <td><?php echo $a['jenis_anggota']; ?></td>
                        <td><?php echo $a['nis'] ?: ($a['nip'] ?: htmlspecialchars($a['bagian_internal'])); ?></td>
                        <td><?php echo htmlspecialchars($a['no_telepon']); ?></td>
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
