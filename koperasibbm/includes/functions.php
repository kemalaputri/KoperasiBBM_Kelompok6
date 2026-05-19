<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['id_user']);
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function role_home_url() {
    $role = getUserRole();

    if($role == 'admin') {
        return base_url() . "/admin/dashboard.php";
    }

    if($role == 'operator') {
        return base_url() . "/operator/dashboard.php";
    }

    if($role == 'user') {
        return base_url() . "/user/index.php";
    }

    return base_url() . "/index.php";
}

function redirect_to_role_home() {
    header("Location: " . role_home_url());
    exit;
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
