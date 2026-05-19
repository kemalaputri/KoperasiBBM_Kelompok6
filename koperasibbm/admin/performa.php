<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

$labels_7_hari = [];
$pesanan_daring = [];
$penjualan_langsung = [];

for($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $labels_7_hari[] = date('d M', strtotime($tanggal));

    $pesanan_daring[] = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE DATE(created_at)='$tanggal'"))['t'] ?? 0);
    $penjualan_langsung[] = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM transaksi_offline WHERE DATE(created_at)='$tanggal'"))['t'] ?? 0);
}

$stok_rendah = mysqli_query($conn, "SELECT nama_produk, stok FROM produk WHERE stok < 20 AND stok > 0 ORDER BY stok ASC LIMIT 5");
$stok_labels = [];
$stok_values = [];
while($s = mysqli_fetch_assoc($stok_rendah)) {
    $stok_labels[] = $s['nama_produk'];
    $stok_values[] = (int)$s['stok'];
}

$top_prod = mysqli_query($conn, "SELECT p.nama_produk, SUM(dp.jumlah) as qty FROM detail_pesanan dp JOIN produk p ON dp.id_produk=p.id_produk GROUP BY dp.id_produk, p.nama_produk ORDER BY qty DESC LIMIT 5");
$top_labels = [];
$top_values = [];
while($t = mysqli_fetch_assoc($top_prod)) {
    $top_labels[] = $t['nama_produk'];
    $top_values[] = (int)$t['qty'];
}

$pengguna_sering_pesan = mysqli_query($conn, "SELECT u.nama_lengkap, COUNT(p.id_pesanan) as total_pesanan FROM pesanan p JOIN users u ON p.id_user=u.id_user WHERE u.role='user' GROUP BY p.id_user, u.nama_lengkap ORDER BY total_pesanan DESC LIMIT 5");
$pengguna_labels = [];
$pengguna_values = [];
while($u = mysqli_fetch_assoc($pengguna_sering_pesan)) {
    $pengguna_labels[] = $u['nama_lengkap'];
    $pengguna_values[] = (int)$u['total_pesanan'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Grafik</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Grafik</h2></div>
        <div class="content-wrapper">
            <p style="margin-bottom:20px; color:var(--text-gray);">Visualisasi data koperasi secara menyeluruh</p>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Pesanan Daring (7 Hari)</h3>
                    <canvas id="chartPesananDaring"></canvas>
                </div>
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Penjualan Langsung (7 Hari)</h3>
                    <canvas id="chartPenjualanLangsung"></canvas>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Stok Barang Rendah (&lt; 20)</h3>
                    <canvas id="chartStokRendah"></canvas>
                </div>
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Produk Terlaris</h3>
                    <canvas id="chartProdukTerlaris"></canvas>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr; gap:20px; margin-top:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px; font-size:0.9rem;">Pengguna yang Sering Memesan</h3>
                    <canvas id="chartPenggunaSeringPesan"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const labels7Hari = <?php echo json_encode($labels_7_hari); ?>;

new Chart(document.getElementById('chartPesananDaring').getContext('2d'), {
    type: 'line',
    data: {
        labels: labels7Hari,
        datasets: [{
            label: 'Pesanan Daring',
            data: <?php echo json_encode($pesanan_daring); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('chartPenjualanLangsung').getContext('2d'), {
    type: 'bar',
    data: {
        labels: labels7Hari,
        datasets: [{
            label: 'Penjualan Langsung',
            data: <?php echo json_encode($penjualan_langsung); ?>,
            backgroundColor: '#10b981',
            borderRadius: 4
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('chartStokRendah').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($stok_labels); ?>,
        datasets: [{
            label: 'Stok',
            data: <?php echo json_encode($stok_values); ?>,
            backgroundColor: '#f59e0b',
            borderRadius: 4
        }]
    },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('chartProdukTerlaris').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($top_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($top_values); ?>,
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
        }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('chartPenggunaSeringPesan').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($pengguna_labels); ?>,
        datasets: [{
            label: 'Jumlah Pesanan',
            data: <?php echo json_encode($pengguna_values); ?>,
            backgroundColor: '#8b5cf6',
            borderRadius: 4
        }]
    },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
});
</script>
</body>
</html>
