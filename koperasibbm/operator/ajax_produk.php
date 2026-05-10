<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { exit; }

header('Content-Type: application/json');
 $term = isset($_GET['term']) ? mysqli_real_escape_string($conn, $_GET['term']) : '';
 $data = [];

if(strlen($term) > 0) {
    $q = mysqli_query($conn, "SELECT id_produk, nama_produk, harga, stok FROM produk WHERE nama_produk LIKE '%$term%' AND stok > 0 LIMIT 10");
    while($r = mysqli_fetch_assoc($q)) {
        $data[] = [
            'id' => $r['id_produk'],
            'nama' => $r['nama_produk'],
            'harga' => $r['harga'],
            'stok' => $r['stok']
        ];
    }
}
echo json_encode($data);
?>