<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'admin') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';
// HAPUS BARANG
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $img_q = mysqli_query($conn, "SELECT gambar FROM produk WHERE id_produk=$id");
    $img_d = mysqli_fetch_assoc($img_q);
    if($img_d['gambar'] != 'default.jpg' && file_exists(__DIR__."/../uploads/produk/".$img_d['gambar'])) {
        unlink(__DIR__."/../uploads/produk/".$img_d['gambar']);
    }
    mysqli_query($conn, "DELETE FROM produk WHERE id_produk=$id");
    $msg = "<div class='alert alert-success'>Barang dihapus!</div>";
}

// TAMBAH BARANG
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kat = (int)$_POST['id_kategori'];
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $gambar = 'default.jpg';
    
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = __DIR__."/../uploads/produk/";
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, ['jpg','jpeg','png'])) {
            $msg = "<div class='alert alert-danger'>Format gambar harus JPG/PNG!</div>";
        } elseif($_FILES['gambar']['size'] > 2000000) {
            $msg = "<div class='alert alert-danger'>Ukuran gambar maks 2MB!</div>";
        } else {
            $gambar = uniqid().".".$ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir.$gambar);
        }
    }
    if(!$msg) {
        mysqli_query($conn, "INSERT INTO produk (id_kategori, nama_produk, harga, stok, deskripsi, gambar) VALUES ('$kat', '$nama', '$harga', '$stok', '$deskripsi', '$gambar')");
        $msg = "<div class='alert alert-success'>Barang ditambahkan!</div>";
    }
}

// EDIT BARANG
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id_produk'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kat = (int)$_POST['id_kategori'];
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $gambar_lama = mysqli_real_escape_string($conn, $_POST['gambar_lama']);
    $gambar = $gambar_lama;

    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = __DIR__."/../uploads/produk/";
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg','jpeg','png']) && $_FILES['gambar']['size'] <= 2000000) {
            if($gambar_lama != 'default.jpg' && file_exists($target_dir.$gambar_lama)) unlink($target_dir.$gambar_lama);
            $gambar = uniqid().".".$ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir.$gambar);
        }
    }
    mysqli_query($conn, "UPDATE produk SET id_kategori='$kat', nama_produk='$nama', harga='$harga', stok='$stok', deskripsi='$deskripsi', gambar='$gambar' WHERE id_produk='$id'");
    $msg = "<div class='alert alert-success'>Barang diupdate!</div>";
}

// EXPORT EXCEL
if(isset($_GET['export'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Data_Barang_Koperasi.xls");
    echo "Nama Produk\tKategori\tHarga\tStok\tStatus\tTanggal Diperbarui\n";
    $res = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori");
    while($r = mysqli_fetch_assoc($res)) {
        $st = $r['stok']==0?'Habis':($r['stok']<=20?'Terbatas':'Tersedia');
        echo $r['nama_produk']."\t".$r['nama_kategori']."\t".$r['harga']."\t".$r['stok']."\t".$st."\t".$r['updated_at']."\n";
    }
    exit;
}

 $kategori = mysqli_query($conn, "SELECT * FROM kategori");
 $produk = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Kelola Barang</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Kelola Barang</h2><a href="?export=1" class="btn btn-success">Ekspor Excel</a></div>
        <div class="content-wrapper">
            <?php echo $msg; ?>
            <div class="card">
                <div class="card-header"><h3>Tambah Barang</h3><button class="btn btn-primary btn-sm" onclick="openModal('modalTambah')">+ Tambah Baru</button></div>
                <table>
                    <thead><tr><th>Gambar</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php while($p = mysqli_fetch_assoc($produk)): 
                        $st = $p['stok']==0?'<span class="badge badge-danger">Habis</span>':($p['stok']<=20?'<span class="badge badge-warning">Terbatas</span>':'<span class="badge badge-success">Tersedia</span>');
                    ?>
                        <tr>
                            <td><img src="<?php echo base_url(); ?>/uploads/produk/<?php echo $p['gambar']; ?>" class="img-preview"></td>
                            <td><?php echo htmlspecialchars($p['nama_produk']); ?></td>
                            <td><?php echo $p['nama_kategori']; ?></td>
                            <td>Rp <?php echo number_format($p['harga'],0,',','.'); ?></td>
                            <td><?php echo $p['stok']; ?></td>
                            <td><?php echo $st; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editBarang(<?php echo $p['id_produk']; ?>, '<?php echo htmlspecialchars($p['nama_produk']); ?>', <?php echo $p['id_kategori']; ?>, <?php echo $p['harga']; ?>, <?php echo $p['stok']; ?>, '<?php echo htmlspecialchars($p['deskripsi']??''); ?>', '<?php echo $p['gambar']; ?>')">Edit</button>
                                <a href="?delete=<?php echo $p['id_produk']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus barang ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modalTambah')">&times;</span>
        <h3 style="margin-bottom:15px;">Tambah Barang</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group"><label>Nama Produk</label><input type="text" name="nama_produk" required></div>
            <div class="form-group"><label>Kategori</label><select name="id_kategori" required><?php mysqli_data_seek($kategori, 0); while($k=mysqli_fetch_assoc($kategori)) echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>"; ?></select></div>
            <div class="form-row">
                <div class="form-group"><label>Harga</label><input type="number" name="harga" required></div>
                <div class="form-group"><label>Stok</label><input type="number" name="stok" required></div>
            </div>
            <div class="form-group"><label>Gambar</label><input type="file" name="gambar" accept="image/jpeg,image/png"></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="3"></textarea></div>
            <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modalEdit')">&times;</span>
        <h3 style="margin-bottom:15px;">Edit Barang</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_produk" id="edit_id">
            <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
            <div class="form-group"><label>Nama Produk</label><input type="text" name="nama_produk" id="edit_nama" required></div>
            <div class="form-group"><label>Kategori</label><select name="id_kategori" id="edit_kategori" required><?php mysqli_data_seek($kategori, 0); while($k=mysqli_fetch_assoc($kategori)) echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>"; ?></select></div>
            <div class="form-row">
                <div class="form-group"><label>Harga</label><input type="number" name="harga" id="edit_harga" required></div>
                <div class="form-group"><label>Stok</label><input type="number" name="stok" id="edit_stok" required></div>
            </div>
            <div class="form-group"><label>Gambar Baru (Kosongkan jika tidak ganti)</label><input type="file" name="gambar" accept="image/jpeg,image/png"></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea></div>
            <button type="submit" name="edit" class="btn btn-primary">Perbarui</button>
        </form>
    </div>
</div>

<script>
function editBarang(id, nama, kat, harga, stok, deskripsi, gambar) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_kategori').value = kat;
    document.getElementById('edit_harga').value = harga;
    document.getElementById('edit_stok').value = stok;
    document.getElementById('edit_deskripsi').value = deskripsi;
    document.getElementById('edit_gambar_lama').value = gambar;
    openModal('modalEdit');
}
</script>
</body>
</html>
