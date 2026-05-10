<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Grafik</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Grafik</h2></div>
        <div class="content-wrapper">
            <p style="margin-bottom:20px; color:var(--text-gray);">Visualisasi data koperasi</p>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Pesanan Online (7 Hari)</h3>
                    <canvas id="chartOnline"></canvas>
                </div>
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Transaksi Offline (7 Hari)</h3>
                    <canvas id="chartOffline"></canvas>
                </div>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Stok Barang Rendah (< 20)</h3>
                    <canvas id="chartStok"></canvas>
                </div>
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Produk Terlaris</h3>
                    <canvas id="chartTerlaris"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Chart Online
new Chart(document.getElementById('chartOnline').getContext('2d'), { 
    type: 'line', data: { 
        labels: [<?php for($i=6;$i>=0;$i--) echo "'".date('d M', strtotime("-$i days"))."',"; ?>], 
        datasets: [{ label: 'Online', data: [<?php for($i=6;$i>=0;$i--) { $t=date('Y-m-d', strtotime("-$i days")); $c=mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE DATE(created_at)='$t'"))['t']??0; echo "$c,"; } ?>], borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true }] }, 
    options: { responsive: true, plugins: { legend: { display: false } } } 
});

// Chart Offline
new Chart(document.getElementById('chartOffline').getContext('2d'), { 
    type: 'bar', data: { 
        labels: [<?php for($i=6;$i>=0;$i--) echo "'".date('d M', strtotime("-$i days"))."',"; ?>], 
        datasets: [{ label: 'Offline', data: [<?php for($i=6;$i>=0;$i--) { $t=date('Y-m-d', strtotime("-$i days")); $c=mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM transaksi_offline WHERE DATE(created_at)='$t'"))['t']??0; echo "$c,"; } ?>], backgroundColor: '#10b981', borderRadius: 4 }] }, 
    options: { responsive: true, plugins: { legend: { display: false } } } 
});

<?php $stok_rendah = mysqli_query($conn, "SELECT nama_produk, stok FROM produk WHERE stok < 20 AND stok > 0 ORDER BY stok ASC LIMIT 5"); ?>
new Chart(document.getElementById('chartStok').getContext('2d'), { 
    type: 'bar', data: { 
        labels: [<?php while($s=mysqli_fetch_assoc($stok_rendah)) echo "'".$s['nama_produk']."',"; ?>], 
        datasets: [{ label: 'Stok', data: [<?php mysqli_data_seek($stok_rendah, 0); while($s=mysqli_fetch_assoc($stok_rendah)) echo $s['stok'].","; ?>], backgroundColor: '#f59e0b', borderRadius: 4 }] }, 
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } } 
});

<?php $terlaris = mysqli_query($conn, "SELECT p.nama_produk, SUM(dp.jumlah) as qty FROM detail_pesanan dp JOIN produk p ON dp.id_produk=p.id_produk GROUP BY dp.id_produk ORDER BY qty DESC LIMIT 5"); ?>
new Chart(document.getElementById('chartTerlaris').getContext('2d'), { 
    type: 'doughnut', data: { 
        labels: [<?php while($t=mysqli_fetch_assoc($terlaris)) echo "'".$t['nama_produk']."',"; ?>], 
        datasets: [{ data: [<?php mysqli_data_seek($terlaris, 0); while($t=mysqli_fetch_assoc($terlaris)) echo $t['qty'].","; ?>], backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'] }] }, 
    options: { responsive: true } 
});
</script>
</body>
</html>