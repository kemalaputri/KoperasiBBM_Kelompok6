<div class="sidebar">
    <div class="sidebar-brand">
        <img src="<?php echo base_url(); ?>/assets/img/logo.png" alt="Logo">
        <h3>Panel Operator<br><small style="font-size:0.7rem; font-weight:400; opacity:0.8;">Koperasi BBM</small></h3>
        <button type="button" class="panel-menu-toggle" id="panelMenuToggle" aria-label="Buka menu operator">☰</button>
    </div>
    
    <div class="sidebar-collapsible" id="panelNav">
    <div class="sidebar-profile">
        <div class="avatar" style="background:var(--warning);">O</div>
        <p style="font-weight:600; color:white;"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Operator'); ?></p>
        <p style="font-size:0.75rem; margin-top:2px;">Operator Koperasi</p>
    </div>

    <ul class="sidebar-menu">
        <li><a href="<?php echo base_url(); ?>/operator/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i>📊</i> Dasbor</a></li>
        <li><a href="<?php echo base_url(); ?>/operator/stok_barang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'stok_barang.php' ? 'active' : ''; ?>"><i>📦</i> Stok Barang</a></li>
        <li><a href="<?php echo base_url(); ?>/operator/pesanan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : ''; ?>"><i>🛒</i> Pesanan</a></li>
        <li><a href="<?php echo base_url(); ?>/operator/qrcode.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'qrcode.php' ? 'active' : ''; ?>"><i>📷</i> QR Code</a></li>
        <li><a href="<?php echo base_url(); ?>/operator/transaksi_offline.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transaksi_offline.php' ? 'active' : ''; ?>"><i>💰</i> Penjualan Langsung</a></li>
        <li><a href="<?php echo base_url(); ?>/operator/laporan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>"><i>📄</i> Laporan</a></li>
        <li><a href="<?php echo base_url(); ?>/operator/grafik.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'grafik.php' ? 'active' : ''; ?>"><i>📈</i> Grafik</a></li>
        <li><a href="<?php echo base_url(); ?>/operator/profil.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>"><i>👤</i> Profil</a></li>
        <li style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);"><a href="<?php echo base_url(); ?>/auth/logout.php" style="color: #fca5a5;"><i>🚪</i> Keluar</a></li>
    </ul>
    </div>
    <script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</div>
