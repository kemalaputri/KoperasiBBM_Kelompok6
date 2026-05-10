<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';

// Aksi Simpan Stok Opname (Massal)
if(isset($_POST['simpan_opname'])) {
    $changes = $_POST['stok_ubah'] ?? []; // Array [id_produk => jumlah_perubahan]
    $sukses = 0;
    
    foreach($changes as $id_produk => $jumlah_ubah) {
        $id_produk = (int)$id_produk;
        $jumlah_ubah = (int)$jumlah_ubah;
        
        if($jumlah_ubah != 0) {
            $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk=$id_produk"));
            $stok_baru = $p['stok'] + $jumlah_ubah;
            
            // Pastikan stok tidak minus
            if($stok_baru < 0) $stok_baru = 0;
            
            mysqli_query($conn, "UPDATE produk SET stok = $stok_baru WHERE id_produk=$id_produk");
            $sukses++;
        }
    }
    
    if($sukses > 0) {
        $msg = "<div class='alert alert-success'>$sukses data stok berhasil diperbarui!</div>";
    } else {
        $msg = "<div class='alert alert-info'>Tidak ada perubahan stok yang disimpan.</div>";
    }
}

 $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
 $kat_id = isset($_GET['id_kategori']) ? (int)$_GET['id_kategori'] : 0;
 $where = "1=1";
if($search) $where .= " AND p.nama_produk LIKE '%$search%'";
if($kat_id > 0) $where .= " AND p.id_kategori=$kat_id";

 $produk = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori WHERE $where ORDER BY p.stok ASC");
 $kategori = mysqli_query($conn, "SELECT * FROM kategori");

// Ambil ulang data produk untuk Modal (tanpa filter agar semua muncul)
 $produk_modal = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori ORDER BY p.nama_produk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Stok Barang</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar">
            <div>
                <h2>Stok Barang</h2>
                <p style="font-size:0.85rem; color:var(--text-gray); margin:0;">Kelola stok produk koperasi</p>
            </div>
            <button onclick="openModal('modalOpname')" class="btn btn-primary">Stok Opname</button>
        </div>
        <div class="content-wrapper">
            <?php echo $msg; ?>
            
            <form method="GET" style="display:flex; gap:10px; margin-bottom:20px; background:white; padding:15px; border-radius:10px; border:1px solid var(--border-color);">
                <input type="text" name="search" placeholder="Cari nama barang..." value="<?php echo htmlspecialchars($search); ?>" style="flex:1; padding:8px; border:1px solid var(--border-color); border-radius:5px;">
                <select name="id_kategori" style="padding:8px; border:1px solid var(--border-color); border-radius:5px;">
                    <option value="0">Semua Kategori</option>
                    <?php while($k = mysqli_fetch_assoc($kategori)): ?>
                    <option value="<?php echo $k['id_kategori']; ?>" <?php echo $kat_id == $k['id_kategori'] ? 'selected' : ''; ?>><?php echo $k['nama_kategori']; ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Update Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($p = mysqli_fetch_assoc($produk)): 
                        $st = $p['stok']==0?'<span class="badge badge-danger">Habis</span>':($p['stok']<=20?'<span class="badge badge-warning">Terbatas</span>':'<span class="badge badge-success">Tersedia</span>');
                    ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($p['nama_produk']); ?></td>
                        <td><?php echo $p['nama_kategori']; ?></td>
                        <td style="font-weight:700; font-size:1.1rem;"><?php echo $p['stok']; ?></td>
                        <td><?php echo $st; ?></td>
                        <td style="color:var(--text-gray); font-size:0.85rem;"><?php echo date('d M Y H:i', strtotime($p['updated_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Stok Opname -->
<div id="modalOpname" class="modal-overlay">
    <div class="modal-content" style="max-width: 700px; max-height: 85vh; display:flex; flex-direction:column;">
        <span class="modal-close" onclick="closeModal('modalOpname')">&times;</span>
        <h3 style="margin-bottom:5px;">Stok Opname</h3>
        <p style="font-size:0.85rem; color:var(--text-gray); margin-bottom:15px;">Isi positif untuk menambah stok, negatif untuk mengurangi. Kosongkan/0 jika tidak diubah.</p>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <input type="text" id="searchOpname" placeholder="Cari nama produk di sini..." style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px;">
        </div>

        <form method="POST" style="overflow-y: auto; flex-grow: 1;">
            <table id="tableOpname">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th style="text-align:center; width:100px;">Stok Saat Ini</th>
                        <th style="text-align:center; width:130px;">+ / - Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($produk_modal)): ?>
                    <tr class="opname-row" data-nama="<?php echo strtolower(htmlspecialchars($p['nama_produk'])); ?>">
                        <td>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($p['nama_produk']); ?></div>
                            <div style="font-size:0.75rem; color:var(--text-gray);"><?php echo $p['nama_kategori']; ?></div>
                        </td>
                        <td style="text-align:center; font-weight:700; font-size:1.1rem;"><?php echo $p['stok']; ?></td>
                        <td style="text-align:center;">
                            <input type="number" name="stok_ubah[<?php echo $p['id_produk']; ?>]" value="0" class="input-opname" style="width:90px; padding:8px; text-align:center; border:1px solid var(--border-color); border-radius:6px; font-weight:600;" min="-10000" max="10000">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="margin-top:20px; padding-top:15px; border-top:1px solid var(--border-color);">
                <button type="submit" name="simpan_opname" class="btn btn-primary" style="width:100%;">Simpan Perubahan Stok</button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-opname:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }
</style>

<!-- PENTING: Load app.js agar fungsi openModal/closeModal berjalan -->
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>

<script>
// Fitur Pencarian di dalam Modal Stok Opname
document.getElementById('searchOpname').addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll('.opname-row');
    
    rows.forEach(row => {
        const nama = row.getAttribute('data-nama');
        if(nama.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

</body>
</html>