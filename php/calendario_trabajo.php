
<?php
session_start();
include("db.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "empleado") {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Europe/Madrid');

setlocale(LC_TIME, 'es_ES.UTF-8');

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = ($mes < 1 || $mes > 12) ? date('m') : $mes;
$anio = ($anio < 2000 || $anio > 2100) ? date('Y') : $anio;

$usuario = $_SESSION["usuario"];
$res = mysqli_query($conn, "SELECT id FROM usuarios WHERE usuario = '$usuario' LIMIT 1");
$usuario_id = mysqli_fetch_assoc($res)['id'];

$primerDiaMes = mktime(0, 0, 0, $mes, 1, $anio);
$ultimoDia = date('t', $primerDiaMes);
$diaSemana = date('w', $primerDiaMes);
$mesNombre = strftime("%B", $primerDiaMes);

$inicioMes = date("$anio-$mes-01");
$finMes = date("$anio-$mes-$ultimoDia");
$query = "SELECT fecha, total_horas, incidente FROM registros WHERE usuario_id = '$usuario_id' AND fecha BETWEEN '$inicioMes' AND '$finMes'";
$resultado = mysqli_query($conn, $query);

$registros = [];
$totalMes = 0;
while ($row = mysqli_fetch_assoc($resultado)) {
    $fecha = $row['fecha'];
    $registros[$fecha] = [
        'horas' => $row['total_horas'],
        'incidente' => $row['incidente'],
    ];
    $totalMes += floatval($row['total_horas']);
}

$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anioAnterior--;
}
$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) {
    $mesSiguiente = 1;
    $anioSiguiente++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>📅 Calendario de trabajo</title>
    <link rel="stylesheet" href="/Style/calendario_trabajo.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📆 Calendario de trabajo - <?php echo ucfirst(strftime("%B %Y", $primerDiaMes)); ?></h2>
        <a href="generar_pdf.php?mes=<?php echo $mes; ?>&anio=<?php echo $anio; ?>" class="btn btn-outline-primary">⬇️ Descargar resumen PDF</a>
        <a href="calendario_empleado.php" class="btn btn-outline-secondary">⬅️ Volver</a>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <a href="?mes=<?php echo $mesAnterior; ?>&anio=<?php echo $anioAnterior; ?>" class="btn btn-sm btn-secondary">⬅️ Mes anterior</a>
        <div>
            <span class="legend-box legend-laborable">Laborable</span>
            <span class="legend-box legend-festivo">Festivo</span>
            <span class="legend-box legend-vacaciones">Vacaciones</span>
            <span class="legend-box legend-dia_baja">Día de baja</span>
            <span class="legend-box legend-descanso">Descanso</span>
            <span class="legend-box legend-no-trabajado">No trabajado</span>
        </div>
        <a href="?mes=<?php echo $mesSiguiente; ?>&anio=<?php echo $anioSiguiente; ?>" class="btn btn-sm btn-secondary">Mes siguiente ➡️</a>
    </div>

    <div class="calendar mb-4">
        <?php
        for ($i = 0; $i < $diaSemana; $i++) echo "<div></div>";

        for ($dia = 1; $dia <= $ultimoDia; $dia++) {
            $fecha = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
            $hoy = date("Y-m-d");
            $tipo = "";
            $horas = "";
            $clase = "day-box";
            $nombre_dia = ucfirst(strftime('%A', strtotime($fecha)));

            if (isset($registros[$fecha])) {
                $horas = floatval($registros[$fecha]['horas']);
                $incidente = $registros[$fecha]['incidente'];

                if (!empty($incidente)) {
                    $tipo = $incidente;
                    $clase .= " $incidente";
                } elseif ($horas > 0) {
                    $tipo = "laborable";
                    $clase .= " laborable";
                } elseif ($fecha < $hoy) {
                    $tipo = "no-trabajado";
                    $clase .= " no-trabajado";
                }
            } else {
                if ($fecha < $hoy) {
                    $tipo = "no-trabajado";
                    $clase .= " no-trabajado";
                }
            }

            echo "<div class='$clase'>
                    <strong>$nombre_dia $dia</strong><br>";
            if ($tipo) {
                echo "<small>$tipo</small><br><strong>{$horas} h</strong>";
            }
            echo "</div>";
        }
        ?>
    </div>

    <div class="text-end">
        <h5>Total horas del mes: <strong><?php echo number_format($totalMes, 2); ?></strong></h5>
    </div>
</div>
</body>
</html>
