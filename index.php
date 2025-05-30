<?php
session_start();
include("db.php");

if (isset($_POST['usuario']) && isset($_POST['clave'])) {
    $usuario = mysqli_real_escape_string($conn, $_POST['usuario']);
    $clave = $_POST['clave'];

    $sql = "SELECT * FROM usuarios WHERE usuario='$usuario' AND clave='$clave'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['tipo'] = $row['tipo'];

        if ($_SESSION['tipo'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "❌ Usuario o clave incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: white;
        }
        .logo {
            display: block;
            margin: 0 auto 1rem;
            height: 100px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <img src="logos.png" alt="Logo Empresa" class="logo">
        <h2>🔐 Iniciar sesión</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Usuario:</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Clave:</label>
                <input type="password" name="clave" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>

</body>
</html>
