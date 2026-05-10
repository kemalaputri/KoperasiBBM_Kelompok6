<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['id_user']);
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getCartCount($conn, $id_user) {
    $query = "SELECT SUM(ki.jumlah) as total FROM keranjang_item ki 
            JOIN keranjang k ON ki.id_keranjang = k.id_keranjang 
            WHERE k.id_user = $id_user";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ? $data['total'] : 0;
}
?>