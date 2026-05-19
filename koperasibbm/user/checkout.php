<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
date_default_timezone_set('Asia/Jakarta');
if(!isLoggedIn() || getUserRole() != 'user') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $id_user = $_SESSION['id_user'];
 $msg = '';

// Proses Pesan Sekarang
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pesan_sekarang'])) {
    $selected_items = $_POST['selected_items'] ?? [];
    $tanggal_ambil = mysqli_real_escape_string($conn, $_POST['tanggal_ambil'] ?? '');
    $jam_ambil = mysqli_real_escape_string($conn, $_POST['jam_ambil'] ?? '');
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
    $total_harga = 0;
    $valid = true;

    if(empty($selected_items) || empty($tanggal_ambil) || empty($jam_ambil)) {
        $msg = "<div class='alert alert-danger'>Data tidak lengkap! Pilih tanggal dan jam pengambilan.</div>";
        $valid = false;
    }

    if($valid) {
        // Cek Kuota Slot
        $slot_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan WHERE tanggal_ambil='$tanggal_ambil' AND jam_ambil='$jam_ambil' AND status <> 'Batal'"))['t'];
        if($slot_count >= 10) {
            $msg = "<div class='alert alert-danger'>Slot waktu ini sudah penuh! Silakan pilih waktu lain.</div>";
            $valid = false;
        }
    }

    if($valid) {
        foreach($selected_items as $id_item) {
            $id_item = (int)$id_item;
            $item_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ki.jumlah, p.harga, p.stok, p.id_produk FROM keranjang_item ki JOIN produk p ON ki.id_produk=p.id_produk WHERE ki.id_item=$id_item"));
            if($item_q['stok'] < $item_q['jumlah']) { $valid = false; $msg = "<div class='alert alert-danger'>Stok tidak cukup!</div>"; break; }
            $total_harga += ($item_q['harga'] * $item_q['jumlah']);
        }
    }

    if($valid) {
        $kode_pesanan = "KOP-".date('Ymd')."-".rand(100000, 999999);
        $metode = "Slot Waktu";
        $jadwal_text = $tanggal_ambil . ' ' . $jam_ambil;
        $status = "Sedang disiapkan";
        
        mysqli_query($conn, "INSERT INTO pesanan (id_user, kode_pesanan, total_harga, status, metode_pengambilan, jadwal_pengambilan, tanggal_ambil, jam_ambil, catatan) 
                             VALUES ('$id_user', '$kode_pesanan', '$total_harga', '$status', '$metode', '$jadwal_text', '$tanggal_ambil', '$jam_ambil', '$catatan')");
        $id_pesanan = mysqli_insert_id($conn);

        foreach($selected_items as $id_item) {
            $id_item = (int)$id_item;
            $item_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ki.jumlah, p.harga, p.id_produk FROM keranjang_item ki JOIN produk p ON ki.id_produk=p.id_produk WHERE ki.id_item=$id_item"));
            mysqli_query($conn, "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_saat_pesan) VALUES ('$id_pesanan', '".$item_q['id_produk']."', '".$item_q['jumlah']."', '".$item_q['harga']."')");
            mysqli_query($conn, "UPDATE produk SET stok = stok - ".$item_q['jumlah']." WHERE id_produk=".$item_q['id_produk']);
            mysqli_query($conn, "DELETE FROM keranjang_item WHERE id_item=$id_item");
        }
        header("Location: " . base_url() . "/user/detail_pesanan.php?id=".$id_pesanan);
        exit;
    }
}

// Ambil item yang dicentang dari halaman keranjang
 $selected_ids = $_POST['selected_items'] ?? [];
 $items = [];
if(!empty($selected_ids)) {
    $ids = implode(',', array_map('intval', $selected_ids));
    $items = mysqli_query($conn, "SELECT ki.*, p.nama_produk, p.harga, p.gambar FROM keranjang_item ki JOIN produk p ON ki.id_produk=p.id_produk WHERE ki.id_item IN ($ids)");
}

 $slots = ['07:00 - 08:00', '08:00 - 09:00', '09:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00', '12:00 - 13:00', '13:00 - 14:00', '14:00 - 15:00'];
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Checkout</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="container" style="padding: 30px 0;">
    <h2 class="section-title">Konfirmasi Pesanan</h2>
    <?php echo $msg; ?>

    <?php if(!$items || mysqli_num_rows($items) == 0): ?>
        <div class="card" style="text-align:center;"><p>Tidak ada produk dipilih atau sesi habis.</p><a href="<?php echo base_url(); ?>/user/keranjang.php" class="btn btn-primary" style="margin-top:10px;">Kembali ke Keranjang</a></div>
    <?php else: ?>
    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:20px; align-items:start;">
        <div class="card" style="margin:0;">
            <h3 style="margin-bottom:15px;">Produk Dipilih</h3>
            <table>
                <thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php $grand_total = 0; while($i = mysqli_fetch_assoc($items)): $sub = $i['harga']*$i['jumlah']; $grand_total+=$sub; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($i['nama_produk']); ?></td>
                        <td>Rp <?php echo number_format($i['harga'],0,',','.'); ?></td>
                        <td><?php echo $i['jumlah']; ?></td>
                        <td>Rp <?php echo number_format($sub,0,',','.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="3" style="text-align:right; font-weight:700;">Total</td><td style="font-weight:700; color:var(--primary);">Rp <?php echo number_format($grand_total,0,',','.'); ?></td></tr>
                </tfoot>
            </table>
        </div>

        <div class="card" style="margin:0;">
            <h3 style="margin-bottom:15px;">Jadwal Pengambilan</h3>
            <form method="POST">
                <?php foreach($selected_ids as $sid): ?>
                    <input type="hidden" name="selected_items[]" value="<?php echo $sid; ?>">
                <?php endforeach; ?>
                
                <div class="form-group">
                    <label>Tanggal Pengambilan <span style="color:red;">*</span></label>
                    <input type="date" name="tanggal_ambil" id="tanggalAmbil" min="<?php echo date('Y-m-d'); ?>" required onchange="loadSlots()">
                </div>
                
                <div class="form-group">
                    <label>Jam Pengambilan <span style="color:red;">*</span></label>
                    <select name="jam_ambil" id="jamAmbil" required>
                        <option value="">Pilih Tanggal Dulu</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan" rows="3" placeholder="Contoh: Tolong packing plastik tebal"></textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <a href="<?php echo base_url(); ?>/user/keranjang.php" class="btn btn-outline" style="flex:1;">Kembali</a>
                    <button type="submit" name="pesan_sekarang" class="btn btn-primary" style="flex:2;">Pesan Sekarang</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
const allSlots = <?php echo json_encode($slots); ?>;
const today = '<?php echo date('Y-m-d'); ?>';
const nowTime = '<?php echo date('H:i'); ?>';

function loadSlots() {
    const dateVal = document.getElementById('tanggalAmbil').value;
    const jamSelect = document.getElementById('jamAmbil');
    jamSelect.innerHTML = '<option value="">Memuat...</option>';

    if(!dateVal) return;

    fetch('<?php echo base_url(); ?>/user/get_slots.php?tanggal=' + dateVal)
    .then(res => res.json())
    .then(data => {
        jamSelect.innerHTML = '<option value="">-- Pilih Jam --</option>';
        allSlots.forEach(slot => {
            const endTime = slot.split(' - ')[1].replace(':', ''); 
            const nowNum = parseInt(nowTime.replace(':', ''));
            const endNum = parseInt(endTime);

            let disabled = false;
            if(dateVal === today && endNum <= nowNum) disabled = true;
            if(data[slot] !== undefined && data[slot] >= 10) disabled = true;

            const opt = document.createElement('option');
            opt.value = slot;
            opt.text = slot + (disabled ? ' (Penuh/Lewat)' : '');
            opt.disabled = disabled;
            jamSelect.appendChild(opt);
        });
    });
}
</script>
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</body>
</html>
