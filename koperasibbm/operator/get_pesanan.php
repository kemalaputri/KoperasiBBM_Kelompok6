<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { exit; }

header('Content-Type: application/json');
 $kode = isset($_GET['kode']) ? trim($_GET['kode']) : '';

if(!empty($kode)) {
    // Cari berdasarkan kode_pesanan secara langsung
    $stmt = $conn->prepare("SELECT p.*, u.nama_lengkap FROM pesanan p JOIN users u ON p.id_user=u.id_user WHERE p.kode_pesanan = ?");
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $d = $result->fetch_assoc();
        $id_pesanan = $d['id_pesanan'];
        
        // Ambil detail produk
        $det_stmt = $conn->prepare("SELECT dp.jumlah, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.id_produk=pr.id_produk WHERE dp.id_pesanan = ?");
        $det_stmt->bind_param("i", $id_pesanan);
        $det_stmt->execute();
        $det_res = $det_stmt->get_result();
        
        $html = "<ul style='margin:0; padding-left:20px;'>";
        while($det = $det_res->fetch_assoc()) {
            $html .= "<li>".htmlspecialchars($det['nama_produk'])." (x".$det['jumlah'].")</li>";
        }
        $html .= "</ul>";

        echo json_encode([
            'status'=>'success', 
            'id'=>$d['id_pesanan'], 
            'kode'=>$d['kode_pesanan'], 
            'nama'=>htmlspecialchars($d['nama_lengkap']), 
            'tanggal'=> date('d M Y', strtotime($d['tanggal_ambil'] ?? $d['created_at'])), // Fallback jika null
            'jam'=> $d['jam_ambil'] ?? '-', 
            'status_pesanan'=>$d['status'],
            'produk_html' => $html
        ]);
    } else {
        // Jika tidak ketemu, coba cari berdasarkan ID langsung (jika input manual angka)
        if(is_numeric($kode)) {
            $id = (int)$kode;
            $stmt_id = $conn->prepare("SELECT p.*, u.nama_lengkap FROM pesanan p JOIN users u ON p.id_user=u.id_user WHERE p.id_pesanan = ?");
            $stmt_id->bind_param("i", $id);
            $stmt_id->execute();
            $res_id = $stmt_id->get_result();
            
            if($res_id->num_rows > 0) {
                $d = $res_id->fetch_assoc();
                $id_pesanan = $d['id_pesanan'];
                $det_stmt = $conn->prepare("SELECT dp.jumlah, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.id_produk=pr.id_produk WHERE dp.id_pesanan = ?");
                $det_stmt->bind_param("i", $id_pesanan);
                $det_stmt->execute();
                $det_res = $det_stmt->get_result();
                
                $html = "<ul style='margin:0; padding-left:20px;'>";
                while($det = $det_res->fetch_assoc()) {
                    $html .= "<li>".htmlspecialchars($det['nama_produk'])." (x".$det['jumlah'].")</li>";
                }
                $html .= "</ul>";

                echo json_encode([
                    'status'=>'success', 
                    'id'=>$d['id_pesanan'], 
                    'kode'=> $d['kode_pesanan'] ?: 'TRS-'.str_pad($d['id_pesanan'], 4, '0', STR_PAD_LEFT), 
                    'nama'=>htmlspecialchars($d['nama_lengkap']), 
                    'tanggal'=> date('d M Y', strtotime($d['tanggal_ambil'] ?? $d['created_at'])), 
                    'jam'=> $d['jam_ambil'] ?? '-', 
                    'status_pesanan'=>$d['status'],
                    'produk_html' => $html
                ]);
            } else {
                echo json_encode(['status'=>'error', 'msg'=>'Pesanan tidak ditemukan']);
            }
        } else {
            echo json_encode(['status'=>'error', 'msg'=>'Pesanan tidak ditemukan']);
        }
    }
} else {
    echo json_encode(['status'=>'error', 'msg'=>'Kode kosong']);
}