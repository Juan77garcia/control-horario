
<?php
session_start();
include("db.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "admin") {
    echo "⛔ Acceso denegado.";
    exit();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_id = intval($_POST["usuario_id"]);
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];

    if ($usuario_id && $fecha_inicio && $fecha_fin) {
        $fecha_actual = strtotime($fecha_inicio);
        $fecha_limite = strtotime($fecha_fin);

        while ($fecha_actual <= $fecha_limite) {
            $fecha = date("Y-m-d", $fecha_actual);

            $check = mysqli_query($conn, "SELECT id FROM registros WHERE usuario_id = $usuario_id AND fecha = '$fecha'");
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "
                    INSERT INTO registros (usuario_id, fecha, incidente, hora_entrada, hora_salida, hora_entrada2, hora_salida2, total_horas)
                    VALUES ($usuario_id, '$fecha', 'vacaciones', '00:00', '00:00', '00:00', '00:00', 0)
                ");
            }

            $fecha_actual = strtotime("+1 day", $fecha_actual);
        }

        $mensaje = "✅ Vacaciones registradas correctamente.";
    } else {
        $mensaje = "❌ Faltan datos en el formulario.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registrar Vacaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <h2 class="mb-4">🏖️ Registrar Vacaciones</h2>
    <a href="admin.php" class="btn btn-secondary mb-4">⬅ Volver al panel</a>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Usuario</label>
            <select name="usuario_id" class="form-select" required>
                <option value="">-- Seleccionar usuario --</option>
                <?php
                $usuarios = mysqli_query($conn, "SELECT id, usuario FROM usuarios ORDER BY usuario ASC");
                while ($u = mysqli_fetch_assoc($usuarios)) {
                    echo "<option value='{$u['id']}'>{$u['usuario']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Fecha de inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Fecha de fin</label>
            <input type="date" name="fecha_fin" class="form-control" required>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Registrar Vacaciones</button>
        </div>
    </form>
</div>
</body>
</html>
