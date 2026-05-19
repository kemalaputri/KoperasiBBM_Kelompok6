<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';

// Hapus Laporan
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM laporan WHERE id_laporan=$id");
    $msg = "<div class='alert alert-success'>Laporan dihapus.</div>";
}

// Ekspor Excel
if(isset($_GET['export_laporan'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Koperasi.xls");
    echo "Operator\tJenis Laporan\tPeriode\tPesanan Daring\tPenjualan Langsung\tTotal Pendapatan\tTanggal Dikirim\n";
    $res = mysqli_query($conn, "SELECT l.*, u.nama_lengkap FROM laporan l JOIN users u ON l.dibuat_oleh=u.id_user WHERE l.sent_at IS NOT NULL ORDER BY l.sent_at DESC");
    while($r = mysqli_fetch_assoc($res)) {
        echo $r['nama_lengkap']."\t".$r['tipe_laporan']."\t".$r['tanggal_mulai']." s/d ".$r['tanggal_selesai']."\t".$r['total_online']."\t".$r['total_offline']."\t".$r['total_pendapatan']."\t".$r['sent_at']."\n";
    }
    exit;
}

// Ambil laporan yang sudah dikirim (sent_at IS NOT NULL)
 $laporan = mysqli_query($conn, "SELECT l.*, u.nama_lengkap FROM laporan l JOIN users u ON l.dibuat_oleh=u.id_user WHERE l.sent_at IS NOT NULL ORDER BY l.sent_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Laporan</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar">
            <h2>Laporan Masuk</h2>
            <a href="?export_laporan=1" class="btn btn-success">Ekspor Excel</a>
        </div>
        <div class="content-wrapper">
            <?php echo $msg; ?>
            
            <div class="card" style="margin:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Operator</th>
                            <th>Jenis Laporan</th>
                            <th>Periode</th>
                            <th>Pesanan Daring</th>
                            <th>Penjualan Langsung</th>
                            <th>Total Pendapatan</th>
                            <th>Tanggal Dikirim</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($l = mysqli_fetch_assoc($laporan)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($l['nama_lengkap']); ?></td>
                        <td><span class="badge badge-info"><?php echo $l['tipe_laporan']; ?></span></td>
                        <td><?php echo date('d M Y', strtotime($l['tanggal_mulai']))." - ".date('d M Y', strtotime($l['tanggal_selesai'])); ?></td>
                        <td>Rp <?php echo number_format($l['total_online'],0,',','.'); ?></td>
                        <td>Rp <?php echo number_format($l['total_offline'],0,',','.'); ?></td>
                        <td style="font-weight:700;">Rp <?php echo number_format($l['total_pendapatan'],0,',','.'); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($l['sent_at'])); ?></td>
                        <td>
                            <button onclick="showDetail('<?php echo $l['nama_lengkap']; ?>', '<?php echo $l['tipe_laporan']; ?>', '<?php echo date('d M Y', strtotime($l['tanggal_mulai']))." - ".date('d M Y', strtotime($l['tanggal_selesai'])); ?>', '<?php echo number_format($l['total_online'],0,',','.'); ?>', '<?php echo number_format($l['total_offline'],0,',','.'); ?>', '<?php echo number_format($l['total_pendapatan'],0,',','.'); ?>')" class="btn btn-sm btn-primary">Detail</button>
                            <a href="?delete=<?php echo $l['id_laporan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus laporan ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($laporan) == 0): ?>
                    <tr><td colspan="8" style="text-align:center; color:var(--text-gray);">Belum ada laporan dari Operator.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Laporan -->
<div id="modalDetail" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modalDetail')">&times;</span>
        <h3 style="margin-bottom:20px;">Detail Laporan</h3>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="color:var(--text-gray);">Operator</span>
            <span id="detOp" style="font-weight:600;"></span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="color:var(--text-gray);">Jenis Laporan</span>
            <span id="detTipe" style="font-weight:600;"></span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="color:var(--text-gray);">Periode</span>
            <span id="detPeriode" style="font-weight:600;"></span>
        </div>
        <hr style="margin:15px 0; border-color:var(--border-color);">
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="color:var(--text-gray);">Pesanan Daring</span>
            <span id="detOn" style="font-weight:600;"></span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="color:var(--text-gray);">Penjualan Langsung</span>
            <span id="detOff" style="font-weight:600;"></span>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-weight:700; margin-top:10px; padding-top:10px; border-top:2px solid var(--border-color);">
            <span>Total Keseluruhan</span>
            <span id="detTotal" style="color:var(--primary);"></span>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
<script>
function showDetail(op, tipe, periode, on, off, total) {
    document.getElementById('detOp').innerText = op;
    document.getElementById('detTipe').innerText = tipe;
    document.getElementById('detPeriode').innerText = periode;
    document.getElementById('detOn').innerText = 'Rp ' + on;
    document.getElementById('detOff').innerText = 'Rp ' + off;
    document.getElementById('detTotal').innerText = 'Rp ' + total;
    openModal('modalDetail');
}
</script>
</body>
</html>
