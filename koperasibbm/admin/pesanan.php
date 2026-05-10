<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
 $where = $filter ? "AND p.status='$filter'" : "";

 $pesanan = mysqli_query($conn, "SELECT p.*, u.nama_lengkap FROM pesanan p JOIN users u ON p.id_user=u.id_user WHERE 1=1 $where ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Pesanan</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Daftar Pesanan</h2></div>
        <div class="content-wrapper">
            <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                <a href="?status=" class="btn <?php echo !$filter ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
                <a href="?status=Sedang disiapkan" class="btn <?php echo $filter=='Sedang disiapkan' ? 'btn-primary' : 'btn-outline'; ?>">Sedang Disiapkan</a>
                <a href="?status=Siap diambil" class="btn <?php echo $filter=='Siap diambil' ? 'btn-primary' : 'btn-outline'; ?>">Siap Ambil</a>
                <a href="?status=Selesai" class="btn <?php echo $filter=='Selesai' ? 'btn-primary' : 'btn-outline'; ?>">Selesai</a>
                <a href="?status=Batal" class="btn <?php echo $filter=='Batal' ? 'btn-primary' : 'btn-outline'; ?>">Batal</a>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Pesanan</th>
                                <th>Nama Pengguna</th>
                                <th>Produk</th>
                                <th>Total Harga</th>
                                <th>Jadwal Pengambilan</th>
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

                            // Logika Badge
                            $badge_class = 'badge-info'; // Sedang disiapkan (Biru)
                            if($p['status'] == 'Siap diambil') $badge_class = 'badge-warning'; // Kuning
                            if($p['status'] == 'Selesai') $badge_class = 'badge-success'; // Hijau
                            if($p['status'] == 'Batal') $badge_class = 'badge-danger'; // Merah
                        ?>
                        <tr>
                            <td><strong style="color:var(--primary);"><?php echo $kode; ?></strong></td>
                            <td><?php echo htmlspecialchars($p['nama_lengkap']); ?></td>
                            <td style="font-size:0.9rem;"><?php echo $prod_string; ?></td>
                            <td>Rp <?php echo number_format($p['total_harga'],0,',','.'); ?></td>
                            <td>
                                <?php echo date('d M Y', strtotime($p['tanggal_ambil'])); ?><br>
                                <small style="color:var(--primary);"><?php echo $p['jam_ambil']; ?></small>
                            </td>
                            <td style="font-size:0.9rem; color:var(--text-gray);"><?php echo htmlspecialchars($p['catatan'] ?? '-'); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $p['status']; ?></span></td>
                            <td>
                                <a href="#" onclick="alert('Fitur detail pesanan akan ditampilkan di sini. Kode: <?php echo $kode; ?>')" class="btn btn-sm btn-primary">Detail</a>
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