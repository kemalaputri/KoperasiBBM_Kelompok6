<div class="sidebar">
    <div class="sidebar-brand">
        <img src="<?php echo base_url(); ?>/assets/img/logo.png" alt="Logo">
        <h3>Admin Panel<br><small style="font-size:0.7rem; font-weight:400; opacity:0.8;">Koperasi BBM</small></h3>
    </div>
    
    <div class="sidebar-profile">
        <div class="avatar">A</div>
        <p style="font-weight:600; color:white;"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></p>
        <p style="font-size:0.75rem; margin-top:2px;">Administrator</p>
    </div>

    <ul class="sidebar-menu">
        <li><a href="<?php echo base_url(); ?>/admin/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i>📊</i> Dashboard</a></li>
        <li><a href="<?php echo base_url(); ?>/admin/barang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'barang.php' ? 'active' : ''; ?>"><i>📦</i> Barang</a></li>
        <li><a href="<?php echo base_url(); ?>/admin/operator.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'operator.php' ? 'active' : ''; ?>"><i>👷</i> Operator</a></li>
        <li><a href="<?php echo base_url(); ?>/admin/anggota.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'anggota.php' ? 'active' : ''; ?>"><i>👥</i> Anggota</a></li>
        <li><a href="<?php echo base_url(); ?>/admin/pesanan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : ''; ?>"><i>🛒</i> Pesanan</a></li>
        <li><a href="<?php echo base_url(); ?>/admin/laporan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>"><i>📄</i> Laporan</a></li>
        <li><a href="<?php echo base_url(); ?>/admin/performa.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'performa.php' ? 'active' : ''; ?>"><i>📈</i> Performa</a></li>
        <li><a href="<?php echo base_url(); ?>/admin/profil.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>"><i>👤</i> Profil</a></li>
        <li style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);"><a href="<?php echo base_url(); ?>/auth/logout.php" style="color: #fca5a5;"><i>🚪</i> Logout</a></li>
    </ul>
    <script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</div>