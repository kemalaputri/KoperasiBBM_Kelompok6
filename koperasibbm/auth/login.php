<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if($_SERVER['REQUEST_METHOD'] != 'POST' && isLoggedIn()) {
    redirect_to_role_home();
}

 $error = "";
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user['password'])) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] == 'admin') {
                header("Location: " . base_url() . "/admin/dashboard.php");
            } elseif($user['role'] == 'operator') {
                header("Location: " . base_url() . "/operator/dashboard.php");
            } else {
                header("Location: " . base_url() . "/user/index.php");
            }
            exit;
        } else {
            $error = "Kata sandi salah!";
        }
    } else {
        $error = "Nama pengguna tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Koperasi BBM</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Masuk Akun</h2>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Pengguna</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Kata Sandi</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Masuk</button>
        </form>
        <div class="auth-link">
            Belum punya akun? <a href="<?php echo base_url(); ?>/auth/register.php">Daftar</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
