<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Ambil data kategori
 $kategori = mysqli_query($conn, "SELECT * FROM kategori");

// Ambil data produk
 $produk = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koperasi BBM - Bina Bangkit Mandiri</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<section class="hero">
    <h1>KOPERASI BINA BANGKIT MANDIRI</h1>   
</section>

<section class="info-section">
    <div class="container info-grid">
        <div class="info-card">
            <div style="font-size: 2rem;">📅</div>
            <h3>Slot Waktu Terjadwal</h3>
            <p>Pilih waktu pengambilan barang sesuai jadwal yang tersedia.</p>
        </div>
        <div class="info-card">
            <div style="font-size: 2rem;">📦</div>
            <h3>Lacak Pesanan</h3>
            <p>Pantau status pesanan secara real-time melalui sistem.</p>
        </div>
        <div class="info-card">
            <div style="font-size: 2rem;">🛍️</div>
            <h3>Pengambilan Mandiri</h3>
            <p>Ambil pesanan langsung di koperasi tanpa layanan pengantaran.</p>
        </div>
    </div>
</section>

<section class="product-section">
    <div class="container">
        <h2 class="section-title">Produk Kami</h2>
        
        <div class="filter-bar">
            <button class="filter-btn active" data-filter="Semua">Semua</button>
            <?php while($kat = mysqli_fetch_assoc($kategori)): ?>
                <button class="filter-btn" data-filter="<?php echo $kat['nama_kategori']; ?>">
                    <?php echo $kat['nama_kategori']; ?>
                </button>
            <?php endwhile; ?>
            <div class="search-box">
                <input type="text" id="searchProduct" placeholder="Cari produk...">
            </div>
        </div>

        <div class="product-grid">
            <?php while($p = mysqli_fetch_assoc($produk)): 
                $stok = $p['stok'];
                if($stok == 0) {
                    $status_class = "stock-habis";
                    $status_text = "Habis";
                } elseif($stok <= 20) {
                    $status_class = "stock-terbatas";
                    $status_text = "Terbatas (stok: $stok)";
                } else {
                    $status_class = "stock-tersedia";
                    $status_text = "Tersedia (stok: $stok)";
                }
            ?>
                <div class="product-card" data-kategori="<?php echo $p['nama_kategori']; ?>">
                    <div class="product-img">
                        <?php if($p['gambar'] != 'default.jpg'): ?>
                            <img src="<?php echo base_url(); ?>/uploads/produk/<?php echo $p['gambar']; ?>" alt="<?php echo $p['nama_produk']; ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            Tidak Ada Gambar
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo $p['nama_produk']; ?></div>
                        <div class="product-price">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></div>
                        <div class="stock-status <?php echo $status_class; ?>"><?php echo $status_text; ?></div>
                        <div class="product-action">
                            <?php if($stok == 0): ?>
                                <button class="btn btn-disabled" disabled>Stok Habis</button>
                            <?php elseif(isLoggedIn() && getUserRole() == 'user'): ?>
                                <!-- Untuk user login, arahkan ke proses keranjang -->
                                <form action="<?php echo base_url(); ?>/user/add_to_cart.php" method="POST">
                                    <input type="hidden" name="id_produk" value="<?php echo $p['id_produk']; ?>">
                                    <button type="submit" class="btn btn-primary">Tambah ke Keranjang</button>
                                </form>
                            <?php else: ?>
                                <a href="<?php echo base_url(); ?>/auth/login.php" class="btn btn-primary">Masuk/Daftar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</body>
</html>