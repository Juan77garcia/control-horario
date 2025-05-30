<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST["usuario"]) &&
        isset($_POST["clave"]) &&
        isset($_POST["tipo"])
    ) {
        $usuario = mysqli_real_escape_string($conn, $_POST["usuario"]);
        $clave = $_POST["clave"]; 
        $tipo = $_POST["tipo"];

        // Verificar si el usuario ya existe
        $sql_check_user = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
        $result_user = mysqli_query($conn, $sql_check_user);

        if (!$result_user) {
            die("Error en la consulta: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result_user) > 0) {
            $mensaje = "El usuario ya existe. Elija otro nombre de usuario.";
        } else {
            // Insertar nuevo usuario sin clave de registro
            $sql = "INSERT INTO usuarios (usuario, clave, tipo) 
                    VALUES ('$usuario', '$clave', '$tipo')";

            if (mysqli_query($conn, $sql)) {
                $mensaje = "Usuario registrado con éxito.";
            } else {
                $mensaje = "Error al registrar: " . mysqli_error($conn);
            }
        }
    } else {
        $mensaje = "Todos los campos son requeridos.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container mt-3">
    <h2>👤 Registro de Usuario</h2>

    <?php 
    if (isset($mensaje)) echo "<div class='alert alert-info'>$mensaje</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Usuario:</label>
            <input type="text" name="usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Clave:</label>
            <input type="password" name="clave" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo de usuario:</label>
            <select name="tipo" class="form-select" required>
                <option value="empleado">Empleado</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button>
        <a href="admin.php" class="btn btn-secondary ms-2">⬅ Volver</a>
    </form>
</div>
</body>
</html>
