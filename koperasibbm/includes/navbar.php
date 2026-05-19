<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

 $role = getUserRole();
 $cart_count = 0;
if(isLoggedIn() && $role == 'user') {
    $cart_count = getCartCount($conn, $_SESSION['id_user']);
}

 $current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <div class="navbar-brand">
        <img src="<?php echo base_url(); ?>/assets/img/logo.png" alt="Logo Koperasi BBM">
        <div class="brand-text">
            Koperasi BBM
            <span>Bina Bangkit Mandiri</span>
        </div>
    </div>
    
    <button class="hamburger" id="hamburgerBtn">☰</button>

    <?php if(isLoggedIn() && $role == 'user'): ?>
    <div class="navbar-menu" id="navMenu">
        <a href="<?php echo base_url(); ?>/user/index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Beranda</a>
        <a href="<?php echo base_url(); ?>/user/riwayat.php" class="nav-link <?php echo $current_page == 'riwayat.php' ? 'active' : ''; ?>">Riwayat</a>
        <a href="<?php echo base_url(); ?>/user/keranjang.php" class="nav-link cart-link <?php echo $current_page == 'keranjang.php' ? 'active' : ''; ?>">
            Keranjang
            <?php if($cart_count > 0): ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="navbar-auth" style="position: relative;">
        <button class="profile-toggle" id="profileToggle">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?></div>
            <span class="profile-name"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            <span style="font-size:0.8rem; color:var(--text-gray);">▼</span>
        </button>
        
        <div class="dropdown-menu" id="profileDropdown">
            <a href="<?php echo base_url(); ?>/user/profil.php">👤 Profil</a>
            <a href="<?php echo base_url(); ?>/auth/logout.php">🚪 Keluar</a>
        </div>
    </div>

    <?php elseif(!isLoggedIn()): ?>
    <div class="navbar-menu" id="navMenu">
        <a href="<?php echo base_url(); ?>/index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Beranda</a>
    </div>
    <div class="navbar-auth">
        <a href="<?php echo base_url(); ?>/auth/login.php" class="btn btn-outline">Masuk</a>
        <a href="<?php echo base_url(); ?>/auth/register.php" class="btn btn-primary">Daftar</a>
    </div>

    <?php else: ?>
    <div class="navbar-auth">
        <a href="<?php echo base_url(); ?>/auth/logout.php" class="btn btn-outline">Keluar</a>
    </div>
    <?php endif; ?>
</nav>
