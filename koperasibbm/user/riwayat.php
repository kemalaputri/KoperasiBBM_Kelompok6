<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
date_default_timezone_set('Asia/Jakarta');
if(!isLoggedIn() || getUserRole() != 'user') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $id_user = $_SESSION['id_user'];

// LOGIKA PEMBATALAN OTOMATIS
// Cek pesanan yang masih aktif tapi sudah lewat jadwalnya
 $active_orders = mysqli_query($conn, "SELECT id_pesanan, tanggal_ambil, jam_ambil FROM pesanan WHERE id_user=$id_user AND status IN ('Sedang disiapkan', 'Siap diambil')");
 $now = new DateTime();

while($ao = mysqli_fetch_assoc($active_orders)) {
    $jam_selesai_str = explode(' - ', $ao['jam_ambil'])[1]; // Ambil jam selesai (contoh: 15:00)
    $waktu_ambil_str = $ao['tanggal_ambil'] . ' ' . $jam_selesai_str;
    $waktu_ambil = new DateTime($waktu_ambil_str);
    
    if($now > $waktu_ambil) {
        // Lewat waktu, batalkan otomatis
        $id_cancel = $ao['id_pesanan'];
        mysqli_query($conn, "UPDATE pesanan SET status='Dibatalkan' WHERE id_pesanan=$id_cancel");
        // Kembalikan stok
        $items_q = mysqli_query($conn, "SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan=$id_cancel");
        while($item = mysqli_fetch_assoc($items_q)) {
            mysqli_query($conn, "UPDATE produk SET stok = stok + ".$item['jumlah']." WHERE id_produk=".$item['id_produk']);
        }
    }
}

 $pesanan = mysqli_query($conn, "SELECT * FROM pesanan WHERE id_user=$id_user ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Riwayat Pesanan</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="container" style="padding: 30px 0;">
    <h2 class="section-title">Riwayat Pesanan</h2>
    
    <?php if(mysqli_num_rows($pesanan) == 0): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <h3>Belum Ada Pesanan</h3>
            <a href="<?php echo base_url(); ?>/user/index.php" class="btn btn-primary" style="margin-top:15px;">Mulai Belanja</a>
        </div>
    <?php endif; ?>

    <?php while($p = mysqli_fetch_assoc($pesanan)): 
        $details = mysqli_query($conn, "SELECT dp.*, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.id_produk=pr.id_produk WHERE dp.id_pesanan=".$p['id_pesanan']);
        
        $badge_class = 'badge-info';
        if($p['status'] == 'Siap diambil') $badge_class = 'badge-warning';
        if($p['status'] == 'Selesai diambil') $badge_class = 'badge-success';
        if($p['status'] == 'Dibatalkan') $badge_class = 'badge-danger';
    ?>
    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <div>
                <h3 style="color:var(--primary);"><?php echo $p['kode_pesanan']; ?></h3>
                <small style="color:var(--text-gray);">Dipesan: <?php echo date('d M Y H:i', strtotime($p['created_at'])); ?></small>
            </div>
            <span class="badge <?php echo $badge_class; ?>"><?php echo $p['status']; ?></span>
        </div>

        <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:15px;">
            <div>
                <?php while($d = mysqli_fetch_assoc($details)): ?>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-color); padding:5px 0; font-size:0.9rem;">
                    <span><?php echo $d['nama_produk']; ?> (x<?php echo $d['jumlah']; ?>)</span>
                    <span>Rp <?php echo number_format($d['harga_saat_pesan']*$d['jumlah'],0,',','.'); ?></span>
                </div>
                <?php endwhile; ?>
                <div style="display:flex; justify-content:space-between; margin-top:10px; font-weight:700;">
                    <span>Total</span>
                    <span>Rp <?php echo number_format($p['total_harga'],0,',','.'); ?></span>
                </div>
            </div>

            <div style="text-align:center; border-left:1px solid var(--border-color); padding-left:15px;">
                <small>Pengambilan</small>
                <p style="font-weight:600; margin:5px 0;"><?php echo date('d M Y', strtotime($p['tanggal_ambil'])); ?></p>
                <p style="font-weight:600; color:var(--primary);"><?php echo $p['jam_ambil']; ?></p>
                
                <?php if($p['status'] == 'Sedang disiapkan' || $p['status'] == 'Siap diambil'): ?>
                    <div id="qr-<?php echo $p['id_pesanan']; ?>" style="display:inline-block; margin-top:10px;"></div>
                    <script>
                        new QRCode(document.getElementById("qr-<?php echo $p['id_pesanan']; ?>"), {
                            text: "<?php echo $p['kode_pesanan']; ?>",
                            width: 80, height: 80,
                            correctLevel : QRCode.CorrectLevel.H
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</body>
</html>