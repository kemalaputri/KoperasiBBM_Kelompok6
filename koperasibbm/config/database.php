<?php
 $host = "localhost";
 $user = "root";
 $pass = "";
 $db   = "koperasibbm";

 $conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function base_url() {
    return "http://localhost/koperasibbm"; // Sesuaikan jika nama folder berbeda
}
?>