<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';
 $id_user = $_SESSION['id_user'];

// Buat Laporan Baru
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buat_laporan'])) {
    $tipe = mysqli_real_escape_string($conn, $_POST['tipe'] ?? '');
    $mulai = '';
    $selesai = '';
    
    if($tipe == 'Harian') {
        $tgl = mysqli_real_escape_string($conn, $_POST['periode_harian'] ?? '');
        if($tgl) { $mulai = $selesai = $tgl; }
    } elseif($tipe == 'Mingguan') {
        $week_val = mysqli_real_escape_string($conn, $_POST['periode_mingguan'] ?? '');
        if($week_val) {
            $year = substr($week_val, 0, 4);
            $week = substr($week_val, 6, 2);
            $dto = new DateTime();
            $dto->setISODate($year, $week);
            $mulai = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $selesai = $dto->format('Y-m-d');
        }
    } elseif($tipe == 'Bulanan') {
        $month_val = mysqli_real_escape_string($conn, $_POST['periode_bulanan'] ?? '');
        if($month_val) {
            $mulai = date('Y-m-01', strtotime($month_val));
            $selesai = date('Y-m-t', strtotime($month_val));
        }
    }

    if($mulai && $selesai) {
        // Hitung Total Online (Status Selesai)
        $total_on = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga),0) as t FROM pesanan WHERE status='Selesai' AND DATE(created_at) BETWEEN '$mulai' AND '$selesai'"))['t'];
        // Hitung Total Offline
        $total_off = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga),0) as t FROM transaksi_offline WHERE DATE(created_at) BETWEEN '$mulai' AND '$selesai'"))['t'];
        $total_all = $total_on + $total_off;
        
        mysqli_query($conn, "INSERT INTO laporan (tipe_laporan, tanggal_mulai, tanggal_selesai, total_pendapatan, total_online, total_offline, dibuat_oleh) VALUES ('$tipe', '$mulai', '$selesai', '$total_all', '$total_on', '$total_off', '$id_user')");
        $msg = "<div class='alert alert-success'>Laporan berhasil dibuat!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Periode laporan tidak valid!</div>";
    }
}

// Kirim ke Admin
if(isset($_GET['kirim_admin'])) {
    $id = (int)$_GET['kirim_admin'];
    mysqli_query($conn, "UPDATE laporan SET sent_at=NOW() WHERE id_laporan=$id AND sent_at IS NULL");
    $msg = "<div class='alert alert-success'>Laporan berhasil dikirim ke Admin!</div>";
}

// Filter Tab
 $filter_tipe = isset($_GET['tipe']) ? mysqli_real_escape_string($conn, $_GET['tipe']) : '';
 $where = $filter_tipe ? "AND l.tipe_laporan='$filter_tipe'" : "";

 $laporan = mysqli_query($conn, "SELECT l.* FROM laporan l WHERE l.dibuat_oleh=$id_user $where ORDER BY l.created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Laporan</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar">
            <div>
                <h2>Laporan</h2>
                <p style="font-size:0.85rem; color:var(--text-gray); margin:0;">Buat dan lihat laporan</p>
            </div>
            <button onclick="openModal('modalLaporan')" class="btn btn-primary">Buat Laporan</button>
        </div>
        <div class="content-wrapper">
            <?php echo $msg; ?>
            
            <div style="display:flex; gap:10px; margin-bottom:20px;">
                <a href="?tipe=" class="btn <?php echo !$filter_tipe ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
                <a href="?tipe=Harian" class="btn <?php echo $filter_tipe=='Harian' ? 'btn-primary' : 'btn-outline'; ?>">Harian</a>
                <a href="?tipe=Mingguan" class="btn <?php echo $filter_tipe=='Mingguan' ? 'btn-primary' : 'btn-outline'; ?>">Mingguan</a>
                <a href="?tipe=Bulanan" class="btn <?php echo $filter_tipe=='Bulanan' ? 'btn-primary' : 'btn-outline'; ?>">Bulanan</a>
            </div>

            <div class="card" style="margin:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Tipe Laporan</th>
                            <th>Periode</th>
                            <th>Pesanan Daring</th>
                            <th>Penjualan Langsung</th>
                            <th>Total Pendapatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($l = mysqli_fetch_assoc($laporan)): ?>
                    <tr>
                        <td><span class="badge badge-info"><?php echo $l['tipe_laporan']; ?></span></td>
                        <td><?php echo date('d M Y', strtotime($l['tanggal_mulai']))." - ".date('d M Y', strtotime($l['tanggal_selesai'])); ?></td>
                        <td>Rp <?php echo number_format($l['total_online'],0,',','.'); ?></td>
                        <td>Rp <?php echo number_format($l['total_offline'],0,',','.'); ?></td>
                        <td style="font-weight:700;">Rp <?php echo number_format($l['total_pendapatan'],0,',','.'); ?></td>
                        <td>
                            <?php if(!empty($l['sent_at'])): ?>
                                <span class="badge badge-success">Terkirim</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Draf</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(empty($l['sent_at'])): ?>
                            <a href="?kirim_admin=<?php echo $l['id_laporan']; ?><?php echo $filter_tipe ? '&tipe='.$filter_tipe : ''; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Kirimkan laporan ini ke Admin?')">Kirimkan ke Admin</a>
                            <?php else: ?>
                                <small style="color:var(--text-gray);"><?php echo date('d M Y H:i', strtotime($l['sent_at'])); ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Laporan -->
<div id="modalLaporan" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modalLaporan')">&times;</span>
        <h3 style="margin-bottom:5px;">Buat Laporan Baru</h3>
        <p style="font-size:0.85rem; color:var(--text-gray); margin-bottom:20px;">Laporan akan dibuat berdasarkan data transaksi yang tersedia untuk periode yang dipilih.</p>
        
        <form method="POST">
            <div class="form-group">
                <label>Jenis Laporan</label>
                <select name="tipe" id="tipeLaporan" required onchange="togglePeriode()">
                    <option value="Harian">Harian</option>
                    <option value="Mingguan">Mingguan</option>
                    <option value="Bulanan">Bulanan</option>
                </select>
            </div>
            
            <div class="form-group" id="periodeHarian">
                <label>Tanggal</label>
                <input type="date" name="periode_harian" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group" id="periodeMingguan" style="display:none;">
                <label>Pilih Minggu</label>
                <input type="week" name="periode_mingguan" value="<?php echo date('Y-\WW'); ?>">
            </div>
            
            <div class="form-group" id="periodeBulanan" style="display:none;">
                <label>Pilih Bulan</label>
                <input type="month" name="periode_bulanan" value="<?php echo date('Y-m'); ?>">
            </div>

            <button type="submit" name="buat_laporan" class="btn btn-primary" style="width:100%;">Buat Laporan</button>
        </form>
    </div>
</div>

<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
<script>
function togglePeriode() {
    const tipe = document.getElementById('tipeLaporan').value;
    document.getElementById('periodeHarian').style.display = (tipe === 'Harian') ? 'block' : 'none';
    document.getElementById('periodeMingguan').style.display = (tipe === 'Mingguan') ? 'block' : 'none';
    document.getElementById('periodeBulanan').style.display = (tipe === 'Bulanan') ? 'block' : 'none';
    
    document.querySelector('[name="periode_harian"]').required = (tipe === 'Harian');
    document.querySelector('[name="periode_mingguan"]').required = (tipe === 'Mingguan');
    document.querySelector('[name="periode_bulanan"]').required = (tipe === 'Bulanan');
}
</script>
</body>
</html>
