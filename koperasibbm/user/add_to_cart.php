<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'user') { header("Location: " . base_url() . "/auth/login.php"); exit; }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_SESSION['id_user'];
    $id_produk = (int)$_POST['id_produk'];

    $cek_keranjang = mysqli_query($conn, "SELECT id_keranjang FROM keranjang WHERE id_user = '$id_user'");
    if(mysqli_num_rows($cek_keranjang) > 0) {
        $keranjang = mysqli_fetch_assoc($cek_keranjang);
        $id_keranjang = $keranjang['id_keranjang'];
    } else {
        mysqli_query($conn, "INSERT INTO keranjang (id_user) VALUES ('$id_user')");
        $id_keranjang = mysqli_insert_id($conn);
    }

    $produk_q = mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk = '$id_produk'");
    $p = mysqli_fetch_assoc($produk_q);
    if($p['stok'] <= 0) {
        $_SESSION['error'] = "Stok habis!";
        header("Location: " . base_url() . "/user/index.php");
        exit;
    }

    $cek_item = mysqli_query($conn, "SELECT id_item, jumlah FROM keranjang_item WHERE id_keranjang = '$id_keranjang' AND id_produk = '$id_produk'");
    if(mysqli_num_rows($cek_item) > 0) {
        $item = mysqli_fetch_assoc($cek_item);
        if(($item['jumlah'] + 1) <= $p['stok']) {
            $new_qty = $item['jumlah'] + 1;
            mysqli_query($conn, "UPDATE keranjang_item SET jumlah = '$new_qty' WHERE id_item = '{$item['id_item']}'");
        }
    } else {
        mysqli_query($conn, "INSERT INTO keranjang_item (id_keranjang, id_produk, jumlah) VALUES ('$id_keranjang', '$id_produk', 1)");
    }
    
    // Arahkan ke halaman keranjang setelah menambah
    header("Location: " . base_url() . "/user/keranjang.php");
    exit;
}
?>