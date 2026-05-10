<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';

// Proses Update Status
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_aksi'])) {
    $id_pesanan = (int)$_POST['id_pesanan'];
    $status = $_POST['status'];

    $allowed_status = ['Sedang disiapkan', 'Siap diambil', 'Selesai', 'Batal'];
    if(in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
        $stmt->bind_param("si", $status, $id_pesanan);
        if($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Status pesanan berhasil diperbarui!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Gagal mengubah status.</div>";
        }
        $stmt->close();
    } else {
        $msg = "<div class='alert alert-danger'>Status tidak valid!</div>";
    }
}

// Logika Filter Tab
 $filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
 $where = $filter ? "AND p.status='$filter'" : "";

 $pesanan = mysqli_query($conn, "SELECT p.*, u.nama_lengkap FROM pesanan p JOIN users u ON p.id_user=u.id_user WHERE 1=1 $where ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Daftar Pesanan Masuk</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Daftar Pesanan Masuk</h2></div>
        <div class="content-wrapper">
            <?php echo $msg; ?>
            
            <!-- Tab Filter Status -->
            <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                <a href="?status=" class="btn <?php echo !$filter ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
                <a href="?status=Sedang disiapkan" class="btn <?php echo $filter=='Sedang disiapkan' ? 'btn-primary' : 'btn-outline'; ?>">Sedang disiapkan</a>
                <a href="?status=Siap diambil" class="btn <?php echo $filter=='Siap diambil' ? 'btn-primary' : 'btn-outline'; ?>">Siap diambil</a>
                <a href="?status=Selesai" class="btn <?php echo $filter=='Selesai' ? 'btn-primary' : 'btn-outline'; ?>">Selesai</a>
                <a href="?status=Batal" class="btn <?php echo $filter=='Batal' ? 'btn-primary' : 'btn-outline'; ?>">Batal</a>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode & Pemesan</th>
                                <th>Produk & Jumlah</th>
                                <th>Jadwal Ambil</th>
                                <th>Catatan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($p = mysqli_fetch_assoc($pesanan)): 
                            $kode = $p['kode_pesanan'] ?: 'TRS-'.str_pad($p['id_pesanan'], 4, '0', STR_PAD_LEFT);
                            
                            $det_q = mysqli_query($conn, "SELECT dp.jumlah, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.id_produk=pr.id_produk WHERE dp.id_pesanan=".$p['id_pesanan']);
                            $prods = [];
                            while($d = mysqli_fetch_assoc($det_q)) { $prods[] = $d['nama_produk']." (x".$d['jumlah'].")"; }
                            $prod_string = implode("<br>", $prods);
                            
                            // Warna Badge Status
                            $badge_class = 'badge-info'; // Default Biru
                            if($p['status'] == 'Siap diambil') $badge_class = 'badge-warning'; // Kuning
                            if($p['status'] == 'Selesai') $badge_class = 'badge-success'; // Hijau
                            if($p['status'] == 'Batal') $badge_class = 'badge-danger'; // Merah
                        ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary);"><?php echo $kode; ?></strong><br>
                                <span style="font-size:0.85rem; color:var(--text-gray);"><?php echo htmlspecialchars($p['nama_lengkap']); ?></span>
                            </td>
                            <td style="font-size:0.9rem;"><?php echo $prod_string; ?></td>
                            <td>
                                <?php echo date('d M Y', strtotime($p['tanggal_ambil'])); ?><br>
                                <small style="color:var(--primary);"><?php echo $p['jam_ambil']; ?></small>
                            </td>
                            <td style="font-size:0.9rem; color:var(--text-gray); max-width:150px;"><?php echo htmlspecialchars($p['catatan'] ?? '-'); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $p['status']; ?></span></td>
                            <td>
                                <form method="POST" style="display:flex; flex-direction:column; gap:5px;">
                                    <input type="hidden" name="id_pesanan" value="<?php echo $p['id_pesanan']; ?>">
                                    <select name="status" style="padding:5px; font-size:0.85rem; border-radius:5px; border:1px solid var(--border-color);">
                                        <option value="Sedang disiapkan" <?php echo $p['status']=='Sedang disiapkan'?'selected':''; ?>>Sedang disiapkan</option>
                                        <option value="Siap diambil" <?php echo $p['status']=='Siap diambil'?'selected':''; ?>>Siap ambil</option>
                                        <option value="Selesai" <?php echo $p['status']=='Selesai'?'selected':''; ?>>Selesai</option>
                                        <option value="Batal" <?php echo $p['status']=='Batal'?'selected':''; ?>>Batal</option>
                                    </select>
                                    <button type="submit" name="simpan_aksi" class="btn btn-sm btn-primary">Simpan Aksi</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>