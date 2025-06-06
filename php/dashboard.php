<?php
session_start();

// Verificar que el usuario esté logueado y sea de tipo 'empleado'
if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "empleado") {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Style/dashboard.css">
    <script src="/js/dashboard.js"></script>
    <title>🕒 Panel de Control - Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card p-4">
        <h2 class="mb-3 text-center">Bienvenido, <strong><?php echo $_SESSION["usuario"]; ?></strong></h2>

        <div class="text-center mb-4">
            <p class="mb-0">⏰ Hora actual:</p>
            <div id="hora" class="clock"></div>
        </div>

        <form action="calendario_empleado.php" method="post">
    <input type="hidden" name="usuario" value="<?php echo $_SESSION["usuario"]; ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <button type="submit" name="accion" value="entrada" class="btn btn-primary">📥 Registrar Entrada</button>
        </div>
        <div class="col-md-6">
            <button type="submit" name="accion" value="salida" class="btn btn-danger">📤 Registrar Salida</button>
        </div>
    </div>
</form>

<!-- Botón Ver Horario separado -->
<div class="row mt-3">
    <div class="col-md-12">
        <a href="calendario_empleado.php" class="btn btn-success w-100">🗓️ Ver Horario</a>
    </div>
</div>


        <div class="logout">
            <a href="lagout.php" class="btn btn-outline-secondary mt-4">Cerrar sesión</a>
        </div>
    </div>
</div>



</body>
</html>
