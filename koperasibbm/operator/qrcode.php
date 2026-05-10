<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>QR Code Scanner</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>QR Code & Barcode Scanner</h2></div>
        <div class="content-wrapper">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="card" style="margin:0;">
                    <h3 style="margin-bottom:15px;">Scan Kamera / Barcode</h3>
                    <div id="reader" style="width:100%;"></div>
                </div>
                
                <div>
                    <div class="card" style="margin:0 0 20px 0;">
                        <h3 style="margin-bottom:15px;">Input Manual</h3>
                        <div class="form-group">
                            <input type="text" id="manualCode" placeholder="Ketik Kode Pesanan (cth: KOP-xxx)" autofocus style="padding:10px; font-size:1rem; width:100%;">
                        </div>
                        <button onclick="cariPesanan(document.getElementById('manualCode').value)" class="btn btn-primary" style="width:100%;">Cari Pesanan</button>
                    </div>

                    <div id="scanResult" class="card" style="margin:0; display:none; border-left:4px solid var(--primary);">
                        <h3 style="margin-bottom:15px;">Detail Pesanan</h3>
                        <div id="resultContent"></div>
                    </div>
                    
                    <div id="errorMsg" class="alert alert-danger" style="display:none; margin-top:15px;">Pesanan tidak ditemukan atau sudah dibatalkan.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const html5QrCode = new Html5Qrcode("reader");
let lastScannedCode = ''; // Variabel untuk mencegah spam scan

Html5Qrcode.getCameras().then(cameras => {
    if (cameras && cameras.length > 0) {
        html5QrCode.start(
          cameras[0].id, 
          { fps: 10, qrbox: { width: 250, height: 250 } },
          onScanSuccess,
          onScanFailure
        ).catch(err => console.log("Kamera error", err));
    }
}).catch(err => console.log("Kamera tidak ditemukan", err));

function onScanSuccess(decodedText) {
    // Jika kode sama dengan sebelumnya, jangan lakukan apa-apa (mencegah spam)
    if (decodedText === lastScannedCode) return;
    
    lastScannedCode = decodedText; // Simpan kode yang baru terdeteksi
    cariPesanan(decodedText);
    
    // Reset lastScannedCode setelah 5 detik agar bisa scan ulang kode yang sama jika gagal
    setTimeout(() => { lastScannedCode = ''; }, 5000);
}

function onScanFailure(error) {
    // Biarkan diam, scanner akan coba lagi di frame berikutnya
}

function cariPesanan(code) {
    if(!code) return;
    
    fetch('<?php echo base_url(); ?>/operator/get_pesanan.php?kode=' + encodeURIComponent(code))
    .then(response => response.json())
    .then(data => {
        document.getElementById('errorMsg').style.display = 'none';
        if(data.status === 'success') {
            document.getElementById('scanResult').style.display = 'block';
            
            let html = `
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:15px;">
                    <div><p style="font-size:0.8rem; color:var(--text-gray);">Kode Pesanan</p><h4>${data.kode}</h4></div>
                    <div><p style="font-size:0.8rem; color:var(--text-gray);">Nama Pengguna</p><h4>${data.nama}</h4></div>
                    <div><p style="font-size:0.8rem; color:var(--text-gray);">Status Saat Ini</p><h4><span class="badge badge-info">${data.status_pesanan}</span></h4></div>
                </div>
                
                <div style="margin-bottom:15px; padding:10px; background:#e0f2fe; border-radius:8px; border:1px solid #bae6fd;">
                    <p style="margin:0 0 5px; font-weight:600; color:#0369a1; font-size:0.9rem;">📅 Jadwal Pengambilan: ${data.tanggal}, ${data.jam}</p>
                    <p style="margin:0; font-size:0.8rem; color:#0c4a6e;">* Jadwal hanya referensi. Anda dapat menyelesaikan pesanan ini kapan saja selama status masih aktif.</p>
                </div>

                <div style="margin-bottom:15px;">
                    <p style="font-size:0.8rem; color:var(--text-gray); margin-bottom:5px;">Daftar Produk:</p>
                    <div style="background:var(--bg-light); padding:10px; border-radius:8px;">${data.produk_html}</div>
                </div>
            `;

            if(data.status_pesanan === 'Sedang disiapkan' || data.status_pesanan === 'Siap diambil') {
                html += `<button onclick="selesaikanPesanan(${data.id}, '${data.kode}')" class="btn btn-success" style="width:100%;">✅ Selesai Diambil</button>`;
            } else if(data.status_pesanan === 'Selesai') {
                html += `<div class="alert alert-success" style="margin:0; text-align:center;">Pesanan sudah Selesai diambil.</div>`;
                // Setelah selesai, reset lastScannedCode agar bisa scan pesanan lain
                lastScannedCode = ''; 
            } else if(data.status_pesanan === 'Batal') {
                html += `<div class="alert alert-danger" style="margin:0; text-align:center;">Pesanan sudah Dibatalkan.</div>`;
                lastScannedCode = ''; 
            }

            document.getElementById('resultContent').innerHTML = html;
        } else {
            document.getElementById('scanResult').style.display = 'none';
            document.getElementById('errorMsg').style.display = 'block';
            lastScannedCode = ''; // Reset agar bisa scan lagi
        }
        
        // Auto-fokus kembali ke input manual
        document.getElementById('manualCode').value = '';
        document.getElementById('manualCode').focus();
    }).catch(err => {
        console.error('Error:', err);
        document.getElementById('errorMsg').style.display = 'block';
        document.getElementById('manualCode').focus();
        lastScannedCode = '';
    });
}

function selesaikanPesanan(idPesanan, kodePesanan) {
    if(!confirm('Selesaikan pesanan ' + kodePesanan + '?')) return;

    fetch('<?php echo base_url(); ?>/operator/selesaikan_pesanan.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_pesanan=' + idPesanan
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            alert('Pesanan berhasil diubah menjadi Selesai!');
            document.getElementById('scanResult').style.display = 'none';
            lastScannedCode = ''; // Reset agar bisa scan pesanan lain
            document.getElementById('manualCode').focus();
        } else {
            alert('Gagal mengubah status.');
        }
    });
}
</script>
</body>
</html>