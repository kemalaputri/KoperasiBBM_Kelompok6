<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { exit; }

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pesanan = (int)($_POST['id_pesanan'] ?? 0);
    
    if($id_pesanan > 0) {
        // Gunakan Prepared Statement
        $stmt = $conn->prepare("UPDATE pesanan SET status = 'Selesai' WHERE id_pesanan = ? AND status NOT IN ('Selesai', 'Batal')");
        $stmt->bind_param("i", $id_pesanan);
        
        if($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal update atau status sudah final.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'ID tidak valid.']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Metode salah.']);
}