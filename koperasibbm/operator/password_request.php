<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if(!isLoggedIn() || getUserRole() != 'operator') { header("Location: " . base_url() . "/auth/login.php"); exit; }

 $msg = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_SESSION['id_user'];
    mysqli_query($conn, "INSERT INTO password_request (id_user) VALUES ('$id_user')");
    $msg = "<div class='alert alert-success'>Request perubahan password telah dikirim ke Admin.</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Request Password - Operator</title><link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css"></head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/../includes/operator_sidebar.php'; ?>
    <div class="main-panel">
        <div class="topbar"><h2>Request Password</h2></div>
        <div class="content-wrapper">
            <?php echo $msg; ?>
            <div class="card" style="max-width:500px;">
                <p>Jika Anda lupa password dan ingin meresetnya, kirimkan request ke Admin.</p>
                <form method="POST" style="margin-top:15px;">
                    <button type="submit" class="btn btn-primary">Kirim Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>