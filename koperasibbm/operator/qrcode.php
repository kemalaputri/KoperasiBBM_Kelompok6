<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>QR Code Scanner</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
<script src="https://unpkg.com/html5-qrcode/html5-qrcode.min.js"></script>
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
                    <div id="scannerStatus" class="alert alert-danger" style="display:none; margin-top:15px;"></div>
                </div>
                
                <div>
                    <div class="card" style="margin:0 0 20px 0;">
                        <h3 style="margin-bottom:15px;">Input Manual</h3>
                        <div class="form-group">
                            <input type="text" id="manualCode" placeholder="Ketik Kode Pesanan (cth: KOP-xxx)" autocomplete="off" autofocus style="padding:10px; font-size:1rem; width:100%;">
                        </div>
                        <button onclick="cariPesanan(document.getElementById('manualCode').value)" class="btn btn-primary" style="width:100%;">Cari Pesanan</button>
                    </div>

                    <div id="scanResult" class="card" style="margin:0; display:none; border-left:4px solid var(--primary); position:relative;">
                        <button type="button" onclick="tutupDetailPesanan()" aria-label="Tutup detail pesanan" title="Tutup" style="position:absolute; top:12px; right:12px; width:28px; height:28px; border:none; border-radius:50%; background:var(--bg-light); color:var(--text-gray); cursor:pointer; font-size:1.2rem; line-height:1; display:flex; align-items:center; justify-content:center;">&times;</button>
                        <h3 style="margin-bottom:15px; padding-right:30px;">Detail Pesanan</h3>
                        <div id="resultContent"></div>
                    </div>
                    
                    <div id="errorMsg" class="alert alert-danger" style="display:none; margin-top:15px;">Pesanan tidak ditemukan atau sudah dibatalkan.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let html5QrCode = null;
let lastScannedCode = ''; // Variabel untuk mencegah spam scan

document.addEventListener('DOMContentLoaded', function() {
    focusManualInput();
    setupManualScannerInput();
    startCameraScanner();
});

function focusManualInput() {
    const input = document.getElementById('manualCode');
    if(input) {
        setTimeout(() => input.focus(), 100);
    }
}

function setupManualScannerInput() {
    const input = document.getElementById('manualCode');
    if(!input) return;

    input.addEventListener('keydown', function(event) {
        if(event.key === 'Enter') {
            event.preventDefault();
            cariPesanan(input.value);
        }
    });

    document.addEventListener('click', function(event) {
        const clickedButton = event.target.closest('button, a, input, textarea, select');
        if(!clickedButton) {
            focusManualInput();
        }
    });
}

function showScannerStatus(message) {
    const status = document.getElementById('scannerStatus');
    if(status) {
        status.textContent = message;
        status.style.display = 'block';
    }
}

function startCameraScanner() {
    if(typeof Html5Qrcode === 'undefined') {
        showScannerStatus('Scanner kamera belum bisa dibuka karena library html5-qrcode gagal dimuat. Pastikan koneksi internet/CDN bisa diakses, atau pakai input manual dengan scanner barcode.');
        return;
    }

    Html5Qrcode.getCameras().then(cameras => {
        if (!cameras || cameras.length === 0) {
            showScannerStatus('Kamera tidak ditemukan. Input manual tetap aktif untuk scanner barcode.');
            focusManualInput();
            return;
        }

        const backCamera = cameras.find(camera => /back|rear|environment|belakang/i.test(camera.label));
        const cameraId = (backCamera || cameras[0]).id;
        const scannerConfig = {};

        if(typeof Html5QrcodeSupportedFormats !== 'undefined') {
            scannerConfig.formatsToSupport = [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E
            ];
        }

        html5QrCode = new Html5Qrcode("reader", scannerConfig);

        html5QrCode.start(
            cameraId,
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            onScanSuccess,
            onScanFailure
        ).then(() => {
            document.getElementById('scannerStatus').style.display = 'none';
            focusManualInput();
        }).catch(err => {
            console.log("Kamera error", err);
            showScannerStatus('Kamera tidak bisa dibuka. Izinkan akses kamera di browser, lalu refresh halaman. Input manual tetap aktif untuk scanner barcode.');
            focusManualInput();
        });
    }).catch(err => {
        console.log("Kamera tidak ditemukan", err);
        showScannerStatus('Kamera tidak bisa diakses. Jika halaman dibuka lewat IP/jaringan, gunakan HTTPS atau localhost. Input manual tetap aktif untuk scanner barcode.');
        focusManualInput();
    });
}

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
    code = (code || '').trim();
    if(!code) {
        focusManualInput();
        return;
    }
    
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
        focusManualInput();
    }).catch(err => {
        console.error('Error:', err);
        document.getElementById('errorMsg').style.display = 'block';
        focusManualInput();
        lastScannedCode = '';
    });
}

function tutupDetailPesanan() {
    document.getElementById('scanResult').style.display = 'none';
    document.getElementById('resultContent').innerHTML = '';
    document.getElementById('errorMsg').style.display = 'none';
    lastScannedCode = '';
    focusManualInput();
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
            focusManualInput();
        } else {
            alert('Gagal mengubah status.');
            focusManualInput();
        }
    });
}
</script>
</body>
</html>
