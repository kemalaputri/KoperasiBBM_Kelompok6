<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $top_prod = mysqli_query($conn, "SELECT p.nama_produk, SUM(dp.jumlah) as qty FROM detail_pesanan dp JOIN produk p ON dp.id_produk=p.id_produk GROUP BY dp.id_produk ORDER BY qty DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Performa</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Performa Sistem</h2></div>
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header"><h3>Top 5 Produk Terlaris</h3></div>
                <canvas id="chartTop" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
const chartTopCtx = document.getElementById('chartTop').getContext('2d');
new Chart(chartTopCtx, { type: 'bar', data: { labels: [<?php $t=mysqli_fetch_assoc($top_prod); /*dummy fetch to reset*/ mysqli_data_seek($top_prod,0); while($t=mysqli_fetch_assoc($top_prod)) echo "'".$t['nama_produk']."',"; ?>], datasets: [{label:'Terjual', data: [<?php mysqli_data_seek($top_prod,0); while($t=mysqli_fetch_assoc($top_prod)) echo $t['qty'].","; ?>], backgroundColor:'#f59e0b'}] } });
</script>
</body>
</html>