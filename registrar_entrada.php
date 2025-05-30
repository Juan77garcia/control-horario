<?php
session_start();
include("db.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "empleado") {
    header("Location: index.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$fecha = $_GET["fecha"] ?? date("Y-m-d");
$hora_actual = date("H:i:s");

// Obtener ID del usuario
$res = mysqli_query($conn, "SELECT id FROM usuarios WHERE usuario='$usuario' LIMIT 1");
$row = mysqli_fetch_assoc($res);
$usuario_id = $row['id'];

// Verificar si ya existe
$check = mysqli_query($conn, "SELECT * FROM registros WHERE usuario_id=$usuario_id AND fecha='$fecha'");
if (mysqli_num_rows($check) > 0) {
    $mensaje = "Ya hay una entrada registrada para esa fecha.";
} else {
    $insert = mysqli_query($conn, "INSERT INTO registros (usuario_id, fecha, hora_entrada)  VALUES ($usuario_id, '$fecha', '$hora_actual')");
    $mensaje = $insert ? "Entrada registrada correctamente." : "Error al registrar.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container">
    <h2>Registro de Entrada</h2>
    <div class="alert alert-info"><?php echo $mensaje; ?></div>
    <a href="calendario_empleado.php" class="btn btn-primary">⬅ Volver al Calendario</a>
</div>
</body>
</html>
