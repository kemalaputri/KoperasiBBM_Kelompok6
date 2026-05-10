<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn()) { echo json_encode(['status'=>'error']); exit; }

header('Content-Type: application/json');
 $id_item = (int)$_GET['id'];
 $qty = (int)$_GET['qty'];

if($qty > 0) {
    mysqli_query($conn, "UPDATE keranjang_item SET jumlah=$qty WHERE id_item=$id_item");
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error']);
}