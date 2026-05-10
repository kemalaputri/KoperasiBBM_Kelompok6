<?php
session_start();
session_destroy();
header("Location: " . "http://localhost/koperasibbm/index.php");
exit;
?>