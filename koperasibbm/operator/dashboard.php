<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }

// Perbaikan Query Status
 $masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE status='Sedang disiapkan'"))['t'] ?? 0;
 $siap = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE status='Siap diambil'"))['t'] ?? 0;
 $selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE status='Selesai'"))['t'] ?? 0;
 $batal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE status='Batal'"))['t'] ?? 0;
 $off = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM transaksi_offline"))['t'] ?? 0;
 $total_trans = $masuk + $siap + $selesai + $off;
 $stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM produk"))['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dashboard Operator</title>
<link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Dashboard Operator</h2><span style="color:var(--text-gray);">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span></div>
        <div class="content-wrapper">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:15px; margin-bottom:25px;">
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#3b82f6;">📥</div><div class="stat-details"><h4><?php echo $masuk; ?></h4><p>Sedang Disiapkan</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#f59e0b;">📦</div><div class="stat-details"><h4><?php echo $siap; ?></h4><p>Siap Diambil</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#10b981;">✅</div><div class="stat-details"><h4><?php echo $selesai; ?></h4><p>Selesai Diambil</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#ef4444;">❌</div><div class="stat-details"><h4><?php echo $batal; ?></h4><p>Dibatalkan</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#8b5cf6;">💰</div><div class="stat-details"><h4><?php echo $off; ?></h4><p>Trans. Offline</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#0f172a;">📊</div><div class="stat-details"><h4><?php echo $total_trans; ?></h4><p>Total Transaksi</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#64748b;">🛍️</div><div class="stat-details"><h4><?php echo $stok; ?></h4><p>Stok Barang</p></div></div></div>
            </div>

            <div class="card" style="margin:0;">
                <div class="card-header"><h3>Grafik Pesanan 7 Hari Terakhir</h3></div>
                <canvas id="pesananChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
const ctx = document.getElementById('pesananChart').getContext('2d');
new Chart(ctx, { 
    type: 'line', 
    data: { 
        labels: [<?php for($i=6;$i>=0;$i--) echo "'".date('d M', strtotime("-$i days"))."',"; ?>], 
        datasets: [{ 
            label: 'Jumlah Pesanan', 
            data: [<?php for($i=6;$i>=0;$i--) { $tgl=date('Y-m-d', strtotime("-$i days")); $c=mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE DATE(created_at)='$tgl'"))['t']??0; echo "$c,"; } ?>], 
            borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.3
        }] 
    }, 
    options: { responsive: true, plugins: { legend: { display: false } } } 
});
</script>
</body>
</html>