<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn()) { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $id_item = (int)$_GET['id'];
mysqli_query($conn, "DELETE FROM keranjang_item WHERE id_item=$id_item");
header("Location: " . base_url() . "/user/keranjang.php");