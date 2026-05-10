<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn()) { exit; }

header('Content-Type: application/json');
 $tanggal = mysqli_real_escape_string($conn, $_GET['tanggal'] ?? '');

 $data = [];
if($tanggal) {
    $q = mysqli_query($conn, "SELECT jam_ambil, COUNT(*) as total FROM pesanan WHERE tanggal_ambil='$tanggal' AND status NOT IN ('Dibatalkan') GROUP BY jam_ambil");
    while($r = mysqli_fetch_assoc($q)) {
        $data[$r['jam_ambil']] = (int)$r['total'];
    }
}
echo json_encode($data);