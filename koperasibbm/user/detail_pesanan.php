<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'user') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $id_pesanan = (int)($_GET['id'] ?? 0);
 $id_user = $_SESSION['id_user'];
 $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pesanan WHERE id_pesanan=$id_pesanan AND id_user=$id_user"));

if(!$p) { header("Location: " . base_url() . "/user/riwayat.php"); exit; }
 $details = mysqli_query($conn, "SELECT dp.*, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.id_produk=pr.id_produk WHERE dp.id_pesanan=$id_pesanan");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Detail Pesanan</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="container" style="padding: 30px 0; max-width:600px; margin:auto;">
    <div class="card" style="text-align:center; margin:0;">
        <div style="font-size:3rem; margin-bottom:10px;"></div>
        <h2 style="color:var(--success); margin-bottom:5px;">Pesanan Berhasil!</h2>
        <p style="color:var(--text-gray); margin-bottom:20px;">Tunjukkan QR Code ini saat mengambil barang.</p>
        
        <div id="qrcode" style="display:inline-block; padding:15px; background:white; border:1px solid var(--border-color); border-radius:10px; margin-bottom:20px;"></div>
        
        <h3 style="color:var(--primary); margin-bottom:20px;"><?php echo $p['kode_pesanan']; ?></h3>

        <div style="text-align:left; border-top:1px solid var(--border-color); padding-top:20px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:var(--text-gray);">Status</span>
                <span class="badge badge-warning"><?php echo $p['status']; ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:var(--text-gray);">Tanggal Pesan</span>
                <span style="font-weight:600;"><?php echo date('d M Y H:i', strtotime($p['created_at'])); ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:var(--text-gray);">Tanggal Ambil</span>
                <span style="font-weight:600;"><?php echo date('d M Y', strtotime($p['tanggal_ambil'])); ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:var(--text-gray);">Jam Ambil</span>
                <span style="font-weight:600;"><?php echo $p['jam_ambil']; ?></span>
            </div>
            
            <h4 style="margin-top:15px; margin-bottom:10px;">Produk:</h4>
            <?php while($d = mysqli_fetch_assoc($details)): ?>
            <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:5px;">
                <span><?php echo $d['nama_produk']; ?> (x<?php echo $d['jumlah']; ?>)</span>
                <span>Rp <?php echo number_format($d['harga_saat_pesan']*$d['jumlah'],0,',','.'); ?></span>
            </div>
            <?php endwhile; ?>

            <div style="display:flex; justify-content:space-between; margin-top:15px; padding-top:10px; border-top:2px solid var(--border-color); font-size:1.1rem; font-weight:700;">
                <span>Total</span>
                <span style="color:var(--primary);">Rp <?php echo number_format($p['total_harga'],0,',','.'); ?></span>
            </div>
        </div>

        <a href="<?php echo base_url(); ?>/user/riwayat.php" class="btn btn-primary" style="width:100%; margin-top:20px;">Lihat Riwayat Pesanan</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
new QRCode(document.getElementById("qrcode"), {
    text: "<?php echo $p['kode_pesanan']; ?>",
    width: 150, height: 150,
    colorDark : "#0f172a", colorLight : "#ffffff",
    correctLevel : QRCode.CorrectLevel.H
});
</script>
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</body>
</html>