<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';
if(isset($_POST['simpan_transaksi'])) {
    $items_json = $_POST['items_json'];
    $items = json_decode($items_json, true);
    $total_transaksi = 0;
    $valid = true;

    if(!empty($items)) {
        foreach($items as $item) {
            $id_produk = (int)$item['id'];
            $jumlah = (int)$item['qty'];
            $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok, harga FROM produk WHERE id_produk=$id_produk"));
            if($p['stok'] < $jumlah) { $valid = false; $msg = "<div class='alert alert-danger'>Stok ".$item['nama']." tidak cukup!</div>"; break; }
            $total_transaksi += ($p['harga'] * $jumlah);
        }

        if($valid) {
            mysqli_query($conn, "INSERT INTO transaksi_offline (id_user, nama_kasir, total_harga) VALUES (NULL, '".mysqli_real_escape_string($conn, $_SESSION['nama_lengkap'])."', '$total_transaksi')");
            foreach($items as $item) {
                $id_produk = (int)$item['id'];
                $jumlah = (int)$item['qty'];
                mysqli_query($conn, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk=$id_produk");
            }
            $msg = "<div class='alert alert-success'>Transaksi sebesar Rp ".number_format($total_transaksi,0,',','.')." berhasil disimpan!</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Tambahkan produk terlebih dahulu!</div>";
    }
}

 $trans_terbaru = mysqli_query($conn, "SELECT * FROM transaksi_offline ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Transaksi Offline</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Transaksi Offline</h2></div>
        <div class="content-wrapper">
            <p style="margin-bottom:15px; color:var(--text-gray);">Catat transaksi penjualan langsung</p>
            <?php echo $msg; ?>
            
            <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:20px; margin-bottom:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Cari Produk</h3>
                    <div class="form-group" style="position:relative;">
                        <input type="text" id="produkSearch" autocomplete="off" placeholder="Ketik nama produk..." style="padding:10px; width:100%;">
                        <div id="searchResults" style="position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--border-color); border-radius:0 0 8px 8px; z-index:10; box-shadow:0 4px 6px rgba(0,0,0,0.1); display:none;"></div>
                    </div>
                </div>
                
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Item Transaksi</h3>
                    <form method="POST" id="formTransaksi">
                        <input type="hidden" name="items_json" id="items_json">
                        <table>
                            <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th>Aksi</th></tr></thead>
                            <tbody id="cartBody"></tbody>
                        </table>
                        <hr style="margin:15px 0; border-color:var(--border-color);">
                        <div style="text-align:right; font-size:1.2rem; font-weight:700; margin-bottom:15px;">
                            Total: Rp <span id="grandTotal">0</span>
                        </div>
                        <button type="submit" name="simpan_transaksi" class="btn btn-success" style="width:100%;">Simpan Transaksi</button>
                    </form>
                </div>
            </div>

            <div class="card" style="margin:0;">
                <h3 style="margin-bottom:15px;">Transaksi Terbaru</h3>
                <table>
                    <thead><tr><th>Kasir</th><th>Total</th><th>Waktu</th></tr></thead>
                    <tbody>
                    <?php while($t = mysqli_fetch_assoc($trans_terbaru)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['nama_kasir']); ?></td>
                        <td>Rp <?php echo number_format($t['total_harga'],0,',','.'); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($t['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
let cart = [];

// AJAX Autocomplete
const searchInput = document.getElementById('produkSearch');
const resultsDiv = document.getElementById('searchResults');

searchInput.addEventListener('input', function() {
    const term = this.value.trim();
    if(term.length < 2) {
        resultsDiv.style.display = 'none';
        resultsDiv.innerHTML = '';
        return;
    }

    fetch('<?php echo base_url(); ?>/operator/ajax_produk.php?term=' + encodeURIComponent(term))
    .then(response => response.json())
    .then(data => {
        resultsDiv.innerHTML = '';
        if(data.length > 0) {
            data.forEach(item => {
                const div = document.createElement('div');
                div.style.padding = '10px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid var(--border-color)';
                div.innerHTML = `<strong>${item.nama}</strong><br><small style="color:var(--text-gray);">Harga: Rp ${Number(item.harga).toLocaleString('id-ID')} | Stok: ${item.stok}</small>`;
                
                div.addEventListener('click', function() {
                    addToCart(item.id, item.nama, item.harga);
                    searchInput.value = '';
                    resultsDiv.style.display = 'none';
                });
                resultsDiv.appendChild(div);
            });
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.innerHTML = '<div style="padding:10px; color:var(--text-gray);">Produk tidak ditemukan</div>';
            resultsDiv.style.display = 'block';
        }
    });
});

function addToCart(id, nama, harga) {
    const existing = cart.find(item => item.id === id);
    if(existing) {
        existing.qty += 1;
    } else {
        cart.push({id, nama, harga, qty: 1});
    }
    renderCart();
}

function changeQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if(item) {
        item.qty += delta;
        if(item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
    }
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cartBody');
    tbody.innerHTML = '';
    let total = 0;
    
    cart.forEach(item => {
        const sub = item.harga * item.qty;
        total += sub;
        tbody.innerHTML += `
            <tr>
                <td>${item.nama}</td>
                <td>Rp ${Number(item.harga).toLocaleString('id-ID')}</td>
                <td>
                    <button type="button" onclick="changeQty('${item.id}', -1)" class="btn btn-sm btn-outline">-</button>
                    ${item.qty}
                    <button type="button" onclick="changeQty('${item.id}', 1)" class="btn btn-sm btn-outline">+</button>
                </td>
                <td>Rp ${sub.toLocaleString('id-ID')}</td>
                <td><button type="button" onclick="changeQty('${item.id}', -${item.qty})" class="btn btn-sm btn-danger">Hapus</button></td>
            </tr>
        `;
    });
    
    document.getElementById('grandTotal').innerText = total.toLocaleString('id-ID');
    document.getElementById('items_json').value = JSON.stringify(cart);
}

document.getElementById('formTransaksi').addEventListener('submit', function(e) {
    if(cart.length === 0) {
        alert('Keranjang kosong!'); 
        e.preventDefault();
    }
});
</script>
</body>
</html>