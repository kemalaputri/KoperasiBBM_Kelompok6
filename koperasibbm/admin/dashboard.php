<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk"))['total'] ?? 0;
 $anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'] ?? 0;
 $operator = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='operator'"))['total'] ?? 0;

 $pesanan_online = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE status='Selesai'"))['t'] ?? 0;
 $trans_offline = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM transaksi_offline"))['t'] ?? 0;
 $total_on = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga),0) as t FROM pesanan WHERE status='Selesai'"))['t'] ?? 0;
 $total_off = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga),0) as t FROM transaksi_offline"))['t'] ?? 0;
 $total_all = $total_on + $total_off;
 $aktivitas = mysqli_query($conn, "SELECT a.*, u.nama_lengkap FROM aktivitas a LEFT JOIN users u ON a.id_user = u.id_user ORDER BY a.created_at DESC LIMIT 5");

function hitung_time_ago($datetime) {
    $waktu = strtotime($datetime); $selisih = time() - $waktu;
    if($selisih < 60) return 'Baru saja'; $menit = floor($selisih / 60);
    if($menit < 60) return $menit . ' menit lalu'; $jam = floor($selisih / 3600);
    if($jam < 24) return $jam . ' jam lalu'; $hari = floor($selisih / 86400);
    if($hari < 7) return $hari . ' hari lalu'; return date('d M Y', $waktu);
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dasbor Admin</title>
<link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Dasbor</h2><span style="color:var(--text-gray); font-size:0.9rem;">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span></div>
        <div class="content-wrapper">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-bottom:25px;">
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#3b82f6;">📦</div><div class="stat-details"><h4><?php echo $barang; ?></h4><p>Total Barang</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#10b981;">👥</div><div class="stat-details"><h4><?php echo $anggota; ?></h4><p>Anggota</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#f59e0b;">👷</div><div class="stat-details"><h4><?php echo $operator; ?></h4><p>Operator</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#8b5cf6;">🛒</div><div class="stat-details"><h4><?php echo $pesanan_online; ?></h4><p>Pesanan Daring</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-icon" style="background:#64748b;">💰</div><div class="stat-card"><div class="stat-details"><h4><?php echo $trans_offline; ?></h4><p>Penjualan Langsung</p></div></div></div>
                <div class="card" style="margin:0;"><div class="stat-card"><div class="stat-icon" style="background:#0f172a;">📊</div><div class="stat-details"><h4>Rp <?php echo number_format($total_all,0,',','.'); ?></h4><p>Total Pendapatan</p></div></div></div>
            </div>
            
            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:25px;">
                <div class="card" style="margin:0;">
                    <div class="card-header"><h3>Statistik Pendapatan</h3></div>
                    <canvas id="transaksiChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const ctx = document.getElementById('transaksiChart').getContext('2d');
new Chart(ctx, { type: 'bar', data: { labels: ['Pesanan Daring', 'Penjualan Langsung', 'Total'], datasets: [{ label: 'Pendapatan (Rp)', data: [<?php echo $total_on; ?>, <?php echo $total_off; ?>, <?php echo $total_all; ?>], backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'], borderRadius: 5 }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
</script>
</body>
</html>
