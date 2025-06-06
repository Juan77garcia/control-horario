<?php
include("db.php");
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin') {
    echo "⛔ Acceso denegado. Solo para administradores.";
    exit();
}

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;
$mes = isset($_GET['mes']) ? $_GET['mes'] : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Estadísticas Detalladas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light p-4">

<div class="container">
    <h2 class="mb-4">📊 Estadísticas Detalladas de Asistencia</h2>
    <a href="admin.php" class="btn btn-secondary mb-3">⬅ Volver al Panel</a>

    <!-- Formulario selección -->
    <form method="GET" class="mb-4 row g-3 align-items-end">
        <div class="col-md-4">
            <label for="usuario_id" class="form-label">Seleccionar usuario</label>
            <select name="usuario_id" id="usuario_id" class="form-select" onchange="this.form.submit()" required>
                <option value="">-- Seleccionar --</option>
                <?php
                $usuarios_query = "SELECT DISTINCT usuarios.id, usuarios.usuario
                                   FROM registros 
                                   JOIN usuarios ON registros.usuario_id = usuarios.id";
                $usuarios_result = mysqli_query($conn, $usuarios_query);

                while ($u = mysqli_fetch_assoc($usuarios_result)) {
                    $selected = ($usuario_id == $u['id']) ? 'selected' : '';
                    echo "<option value=\"{$u['id']}\" $selected>{$u['usuario']}</option>";
                }
                ?>
            </select>
        </div>

        <?php if ($usuario_id): ?>
        <div class="col-md-4">
            <label for="mes" class="form-label">Seleccionar mes</label>
            <select name="mes" id="mes" class="form-select" required>
                <option value="">-- Seleccionar mes --</option>
                <?php
                $meses_query = "SELECT DISTINCT DATE_FORMAT(fecha, '%Y-%m') AS mes 
                                FROM registros 
                                WHERE usuario_id = $usuario_id 
                                ORDER BY mes DESC";
                $meses_result = mysqli_query($conn, $meses_query);

                while ($m = mysqli_fetch_assoc($meses_result)) {
                    $selected = ($mes == $m['mes']) ? 'selected' : '';
                    echo "<option value=\"{$m['mes']}\" $selected>{$m['mes']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Ver</button>
        </div>
        <?php endif; ?>
    </form>

    <!-- Resultados detallados -->
    <?php
    if ($usuario_id && $mes):
        $detalle_query = "
            SELECT 
                usuarios.usuario,
                registros.fecha,
                registros.hora_entrada,
                registros.hora_salida,
                registros.hora_entrada2,
                registros.hora_salida2,
                registros.total_horas,
                registros.incidente
            FROM registros
            JOIN usuarios ON registros.usuario_id = usuarios.id
            WHERE registros.usuario_id = $usuario_id
              AND DATE_FORMAT(registros.fecha, '%Y-%m') = '$mes'
            ORDER BY registros.fecha ASC
        ";

        $result = mysqli_query($conn, $detalle_query);

        if (mysqli_num_rows($result) > 0):
            $usuario_nombre = mysqli_fetch_assoc(mysqli_query($conn, "SELECT usuario FROM usuarios WHERE id = $usuario_id"))['usuario'];
    ?>
        <div class="alert alert-info">
            Mostrando días trabajados por <strong><?= $usuario_nombre ?></strong> en el mes <strong><?= $mes ?></strong>.
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Entrada 1</th>
                    <th>Salida 1</th>
                    <th>Entrada 2</th>
                    <th>Salida 2</th>
                    <th>Incidente</th>
                    <th>Total Horas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                mysqli_data_seek($result, 0); // Reiniciar puntero
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['fecha']}</td>
                            <td>{$row['hora_entrada']}</td>
                            <td>{$row['hora_salida']}</td>
                            <td>{$row['hora_entrada2']}</td>
                            <td>{$row['hora_salida2']}</td>
                            <td>{$row['incidente']}</td>
                            <td>{$row['total_horas']}</td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    <?php
        else:
            echo "<div class='alert alert-warning'>Este usuario no tiene registros en ese mes.</div>";
        endif;
    endif;
    ?>
</div>
</body>
</html>
