<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'user') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $id_user = $_SESSION['id_user'];
 $cart_q = mysqli_query($conn, "SELECT ki.*, p.nama_produk, p.harga, p.gambar, p.stok FROM keranjang_item ki JOIN produk p ON ki.id_produk=p.id_produk JOIN keranjang k ON ki.id_keranjang=k.id_keranjang WHERE k.id_user=$id_user");
?>

<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Keranjang</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding: 30px 0;">
    <h2 class="section-title">Keranjang Belanja</h2>
    
    <?php if(mysqli_num_rows($cart_q) == 0): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <h3>Keranjang Anda Kosong</h3>
            <p style="margin:10px 0 20px; color:var(--text-gray);">Yuk mulai belanja!</p>
            <a href="<?php echo base_url(); ?>/user/index.php" class="btn btn-primary">Tambah Produk Lain</a>
        </div>
    <?php else: ?>
    <form id="formCheckout" action="<?php echo base_url(); ?>/user/checkout.php" method="POST">
        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; align-items:start;">
            <!-- Card 1: Produk di Keranjang -->
            <div class="card" style="margin:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding-bottom:10px; margin-bottom:15px;">
                    <label style="font-weight:600; display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" id="selectAll" checked onchange="toggleSelectAll()"> Pilih Semua
                    </label>
                    <span style="color:var(--text-gray); font-size:0.9rem;">Hapus</span>
                </div>

                <?php while($item = mysqli_fetch_assoc($cart_q)): ?>
                <div class="cart-item" style="display:flex; gap:15px; border-bottom:1px solid var(--border-color); padding:15px 0; align-items:center;">
                    <input type="checkbox" class="item-check" name="selected_items[]" value="<?php echo $item['id_item']; ?>" data-price="<?php echo $item['harga']; ?>" data-qty="<?php echo $item['jumlah']; ?>" checked onchange="updateSummary()">
                    
                    <div style="width:80px; height:80px; border-radius:8px; overflow:hidden; flex-shrink:0; background:var(--bg-light);">
                        <?php if($item['gambar'] != 'default.jpg'): ?>
                        <img src="<?php echo base_url(); ?>/uploads/produk/<?php echo $item['gambar']; ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: echo "<div style='display:flex;align-items:center;justify-content:center;height:100%;font-size:0.7rem;color:var(--text-gray);'>No Img</div>"; endif; ?>
                    </div>

                    <div style="flex-grow:1;">
                        <div style="font-weight:600; margin-bottom:5px;"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                        <div style="color:var(--primary); font-weight:700;">Rp <?php echo number_format($item['harga'],0,',','.'); ?></div>
                        
                        <div style="display:flex; align-items:center; gap:10px; margin-top:10px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="updateQty(<?php echo $item['id_item']; ?>, -1)">-</button>
                            <input type="number" id="qty-<?php echo $item['id_item']; ?>" value="<?php echo $item['jumlah']; ?>" min="1" max="<?php echo $item['stok']; ?>" class="qty-input" readonly style="width:40px; text-align:center; border:1px solid var(--border-color); border-radius:4px; padding:2px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="updateQty(<?php echo $item['id_item']; ?>, 1)">+</button>
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <div id="subtotal-<?php echo $item['id_item']; ?>" style="font-weight:700; margin-bottom:10px;">Rp <?php echo number_format($item['harga'] * $item['jumlah'],0,',','.'); ?></div>
                        <a href="<?php echo base_url(); ?>/user/delete_cart.php?id=<?php echo $item['id_item']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus item ini?')">🗑️</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Card 2: Ringkasan Pesanan -->
            <div class="card" style="margin:0; position:sticky; top:80px;">
                <h3 style="margin-bottom:15px;">Ringkasan Pesanan</h3>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span>Total Item</span>
                    <span id="totalItems" style="font-weight:600;">0</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:20px; font-size:1.2rem; font-weight:700; border-top:1px solid var(--border-color); padding-top:10px;">
                    <span>Total Harga</span>
                    <span id="totalPrice" style="color:var(--primary);">Rp 0</span>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-bottom:10px;">Lanjutkan Pemesanan</button>
                <a href="<?php echo base_url(); ?>/user/index.php" class="btn btn-outline" style="width:100%;">Tambah Produk Lain</a>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
function updateQty(idItem, delta) {
    let input = document.getElementById('qty-' + idItem);
    let newVal = parseInt(input.value) + delta;
    let max = parseInt(input.max);
    if(newVal < 1) return;
    if(newVal > max) { alert('Stok tidak mencukupi!'); return; }
    
    // Kirim request update ke DB via AJAX
    fetch('<?php echo base_url(); ?>/user/update_cart.php?id=' + idItem + '&qty=' + newVal)
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            input.value = newVal;
            // Update atribut qty pada checkbox untuk kalkulasi ringkasan
            let chk = document.querySelector(`input[value="${idItem}"]`);
            chk.setAttribute('data-qty', newVal);
            
            // Update subtotal baris ini
            let price = parseFloat(chk.getAttribute('data-price'));
            document.getElementById('subtotal-' + idItem).innerText = 'Rp ' + (price * newVal).toLocaleString('id-ID');
            
            updateSummary();
            // Update badge keranjang di navbar
            let badges = document.querySelectorAll('.cart-badge');
            badges.forEach(b => { let v = parseInt(b.innerText) + delta; b.innerText = v; if(v == 0) b.style.display='none'; });
        }
    });
}

function updateSummary() {
    let checks = document.querySelectorAll('.item-check:checked');
    let totalItems = 0; let totalPrice = 0;
    checks.forEach(chk => {
        let qty = parseInt(chk.getAttribute('data-qty'));
        let price = parseFloat(chk.getAttribute('data-price'));
        totalItems += qty;
        totalPrice += (price * qty);
    });
    document.getElementById('totalItems').innerText = totalItems;
    document.getElementById('totalPrice').innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
}

function toggleSelectAll() {
    let selectAll = document.getElementById('selectAll').checked;
    document.querySelectorAll('.item-check').forEach(chk => { chk.checked = selectAll; });
    updateSummary();
}

// Init
updateSummary();
</script>
<script src="<?php echo base_url(); ?>/assets/js/app.js"></script>
</body>
</html>