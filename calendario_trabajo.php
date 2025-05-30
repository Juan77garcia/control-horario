<?php
session_start();
include("db.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "empleado") {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Europe/Madrid');

// Obtener mes y año actuales o desde parámetros GET
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// Asegurar que estén en rango válido
$mes = ($mes < 1 || $mes > 12) ? date('m') : $mes;
$anio = ($anio < 2000 || $anio > 2100) ? date('Y') : $anio;

// Obtener ID del usuario
$usuario = $_SESSION["usuario"];
$res = mysqli_query($conn, "SELECT id FROM usuarios WHERE usuario = '$usuario' LIMIT 1");
$usuario_id = mysqli_fetch_assoc($res)['id'];

// Calcular días del mes
$primerDiaMes = mktime(0, 0, 0, $mes, 1, $anio);
$ultimoDia = date('t', $primerDiaMes);
$diaSemana = date('w', $primerDiaMes); // 0 domingo
$mesNombre = strftime("%B", $primerDiaMes);

// Obtener registros de ese mes
$inicioMes = date("$anio-$mes-01");
$finMes = date("$anio-$mes-$ultimoDia");
$query = "SELECT fecha, total_horas, incidente FROM registros WHERE usuario_id = '$usuario_id' AND fecha BETWEEN '$inicioMes' AND '$finMes'";
$resultado = mysqli_query($conn, $query);

// Cargar registros en array
$registros = [];
$totalMes = 0;
while ($row = mysqli_fetch_assoc($resultado)) {
    $fecha = $row['fecha'];
    $registros[$fecha] = [
        'horas' => $row['total_horas'],
        'incidente' => $row['incidente'] ?? 'laborable',
    ];
    $totalMes += floatval($row['total_horas']);
}

// Calcular enlaces de navegación
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .day-box {
            height: 110px;
            padding: 8px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            font-size: 0.85rem;
            position: relative;
        }

        .day-box span {
            font-weight: bold;
        }

        .laborable { background-color: #e6f4ea; }
        .festivo { background-color: #fde2e2; }
        .vacaciones { background-color: #fff4cc; }
        .dia_baja { background-color: #dee6f7; }
        .hoy { border: 2px solid #007bff; }

        .legend-box {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 10px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .legend-laborable { background: #e6f4ea; }
        .legend-festivo { background: #fde2e2; }
        .legend-vacaciones { background: #fff4cc; }
        .legend-dia_baja { background: #dee6f7; }
    </style>
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
            </div>
            <a href="?mes=<?php echo $mesSiguiente; ?>&anio=<?php echo $anioSiguiente; ?>" class="btn btn-sm btn-secondary">Mes siguiente ➡️</a>
        </div>

        <div class="calendar mb-4">
            <?php
            // Espacios vacíos al inicio
            for ($i = 0; $i < $diaSemana; $i++) echo "<div></div>";

            for ($dia = 1; $dia <= $ultimoDia; $dia++) {
                $fecha = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
                $tipo = $registros[$fecha]['incidente'] ?? 'laborable';
                $horas = $registros[$fecha]['horas'] ?? 0;

                $clase = "day-box $tipo";
                if ($fecha == date('Y-m-d')) $clase .= " hoy";

                echo "<div class='$clase'>
                    <span>$dia</span><br>
                    <small>$tipo</small><br>
                    <strong>$horas h</strong>
                </div>";
            }
            ?>
        </div>

        <div class="text-end">
            <h5>🧮 Total horas del mes: <strong><?php echo number_format($totalMes, 2); ?></strong></h5>
        </div>
    </div>
</body>
</html>
