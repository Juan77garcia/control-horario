<?php
session_start();
include("db.php");


if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "empleado") {
    header("Location: index.php");
    exit();
}

// BLOQUE REGISTRO DE ENTRADA / SALIDA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["accion"])) {
    date_default_timezone_set('Europe/Madrid');

    $accion = $_POST["accion"];
    $fecha_actual = date("Y-m-d");
    $hora_actual = date("H:i:s");

    $usuario = $_SESSION["usuario"];
    $res = mysqli_query($conn, "SELECT id FROM usuarios WHERE usuario = '$usuario' LIMIT 1");
    $usuario_id = mysqli_fetch_assoc($res)['id'];

    $registro = mysqli_query($conn, "SELECT * FROM registros WHERE usuario_id = '$usuario_id' AND fecha = '$fecha_actual'");
    $existe = mysqli_fetch_assoc($registro);

    if ($accion === "entrada") {
        if (!$existe) {
            mysqli_query($conn, "INSERT INTO registros (usuario_id,  fecha, hora_entrada) VALUES ('$usuario_id', '$fecha_actual', '$hora_actual')");
        } else if (empty($existe['hora_entrada'])) {
            mysqli_query($conn, "UPDATE registros SET hora_entrada = '$hora_actual' WHERE usuario_id = '$usuario_id' AND fecha = '$fecha_actual'");
        } else if (empty($existe['hora_entrada2'])) {
            mysqli_query($conn, "UPDATE registros SET hora_entrada2 = '$hora_actual' WHERE usuario_id = '$usuario_id' AND fecha = '$fecha_actual'");
        }
    }

    if ($accion === "salida") {
        if ($existe && empty($existe['hora_salida'])) {
            mysqli_query($conn, "UPDATE registros SET hora_salida = '$hora_actual' WHERE usuario_id = '$usuario_id' AND fecha = '$fecha_actual'");
        } else if ($existe && empty($existe['hora_salida2'])) {
            mysqli_query($conn, "UPDATE registros SET hora_salida2 = '$hora_actual' WHERE usuario_id = '$usuario_id' AND fecha = '$fecha_actual'");
        }
    }

    header("Location: calendario_empleado.php");
    exit();
}

// DATOS PARA MOSTRAR CALENDARIO
$usuario = $_SESSION["usuario"];
$res = mysqli_query($conn, "SELECT id FROM usuarios WHERE usuario = '$usuario' LIMIT 1");
$usuario_id = mysqli_fetch_assoc($res)['id'];

$mes_actual = date("m");
$anio_actual = date("Y");

$primer_dia_del_mes = strtotime("first day of $anio_actual-$mes_actual");
$ultimo_dia_del_mes = strtotime("last day of $anio_actual-$mes_actual");

// PROCESAR GUARDADO DE EDICIONES
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["guardar"])) {
    $fecha = $_POST["fecha"];
    $hora_salida = $_POST["hora_salida"];
    $hora_entrada2 = $_POST["hora_entrada2"];
    $hora_salida2 = $_POST["hora_salida2"];
    $incidente = mysqli_real_escape_string($conn, $_POST["incidente"]);

    // Si es festivo, vacaciones o baja -> todo a 00:00
    if (in_array($incidente, ['festivo', 'vacaciones', 'dia_baja'])) {
        $hora_entrada = '00:00';
        $hora_salida = '00:00';
        $hora_entrada2 = '00:00';
        $hora_salida2 = '00:00';
    } else {
        $resEntrada = mysqli_query($conn, "SELECT hora_entrada FROM registros WHERE usuario_id = '$usuario_id' AND fecha = '$fecha'");
        $entrada_row = mysqli_fetch_assoc($resEntrada);
        $hora_entrada = $entrada_row["hora_entrada"];
    }

    $total = "NULL";
    if ($hora_entrada && $hora_salida) {
        $total = round((strtotime($hora_salida) - strtotime($hora_entrada)) / 3600, 2);
    }

    $total_turno2 = 0;
    if ($hora_entrada2 && $hora_salida2) {
        $total_turno2 = round((strtotime($hora_salida2) - strtotime($hora_entrada2)) / 3600, 2);
    }

    $total_final = $total + $total_turno2;

    $update = "UPDATE registros 
               SET hora_entrada = '$hora_entrada', hora_salida = '$hora_salida', hora_entrada2 = '$hora_entrada2', hora_salida2 = '$hora_salida2', incidente = '$incidente', total_horas = $total_final 
               WHERE usuario_id = '$usuario_id' AND fecha = '$fecha'";

    if (!mysqli_query($conn, $update)) {
        echo "<div class='alert alert-danger'>Error al guardar cambios: " . mysqli_error($conn) . "</div>";
    } else {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// OBTENER REGISTROS
$query = "SELECT fecha, hora_entrada, hora_salida, hora_entrada2, hora_salida2, total_horas, incidente 
          FROM registros 
          WHERE usuario_id = '$usuario_id' 
          ORDER BY fecha DESC";
$result = mysqli_query($conn, $query);
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>📅 Mi Calendario de Asistencias</title>
    <link rel="stylesheet" href="/Style/calendario_empleado.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="header-bar">
    <h2>🗓️ Historial de asistencia de <?php echo $_SESSION["usuario"]; ?></h2>

    <div class="dropdown">
        <button class="icon-dropdown" type="button" id="menuOpciones" data-bs-toggle="dropdown" aria-expanded="false">
            &#x22EE; <!-- ⋮ -->
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuOpciones">
            <li>
                <form action="calendario_trabajo.php" method="get">
                    <button type="submit" class="btn btn-primary">📆 Calendario de trabajo</button>
                </form>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="lagout.php">🚪 Cerrar sesión</a>
            </li>
        </ul>
    </div>
</div>


</div>
        <h3 style="text-align: center;">Calendario de Asistencia - <?php echo strftime("%B %Y", strtotime("$anio_actual-$mes_actual-01")); ?></h3>
        
        <div class="table-wrapper">
    <table class="table">

                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Día</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Entrada 2</th>
                    <th>Salida 2</th>
                    <th>Total horas</th>
                    <th>Incidente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                setlocale(LC_TIME, "es_ES.UTF-8");

                // Recorremos todos los días del mes
                for ($i = $primer_dia_del_mes; $i <= $ultimo_dia_del_mes; $i = strtotime("+1 day", $i)) {
                    $fecha = date("Y-m-d", $i);
                    $dia = ucfirst(strftime("%A", $i));
                    $fecha_formato = date("d-M", $i);

                    // Buscar los registros de la fecha
                    $query_registro = "SELECT fecha, hora_entrada, hora_salida, hora_entrada2, hora_salida2, total_horas, incidente 
                                       FROM registros 
                                       WHERE usuario_id = '$usuario_id' AND fecha = '$fecha'";
                    $res_registro = mysqli_query($conn, $query_registro);
                    $registro = mysqli_fetch_assoc($res_registro);

                    $entrada = isset($registro["hora_entrada"]) ? substr($registro["hora_entrada"], 0, 5) : '';
                    $salida = isset($registro["hora_salida"]) ? substr($registro["hora_salida"], 0, 5) : '';
                    $entrada2 = isset($registro["hora_entrada2"]) ? substr($registro["hora_entrada2"], 0, 5) : '';
                    $salida2 = isset($registro["hora_salida2"]) ? substr($registro["hora_salida2"], 0, 5) : '';
                    $incidente = $registro["incidente"] ?? '';
                    $total = $registro["total_horas"] ?? 0;


                    echo "<tr>
                        <form method='post'>
                            <td><input type='hidden' name='fecha' value='$fecha'>$fecha_formato</td>
                            <td>$dia</td>
                            <td><input type='time' name='hora_entrada' value='$entrada' class='form-control form-control-sm' readonly></td>
                            <td><input type='time' name='hora_salida' value='$salida' class='form-control form-control-sm'></td>
                            <td><input type='time' name='hora_entrada2' value='$entrada2' class='form-control form-control-sm'></td>
                            <td><input type='time' name='hora_salida2' value='$salida2' class='form-control form-control-sm'></td>
                            <td>$total</td>
                            <td>
                                <select name='incidente' class='form-control form-control-sm'>
                                    <option value=''>Seleccionar</option>
                                    <option value='festivo' " . ($incidente == 'festivo' ? 'selected' : '') . ">Festivo</option>
                                    <option value='vacaciones' " . ($incidente == 'vacaciones' ? 'selected' : '') . ">Vacaciones</option>
                                    <option value='dia_baja' " . ($incidente == 'dia_baja' ? 'selected' : '') . ">Día de baja</option>
                                </select>
                            </td>
                            <td><button type='submit' name='guardar' class='btn btn-success btn-sm'>Guardar</button></td>
                        </form>
                    </tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="lagout.php" class="btn btn-secondary mt-3">Cerrar sesión</a>
    </div>
</body>
</html>
