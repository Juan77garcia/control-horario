<?php
require_once __DIR__ . '/../vendor/autoload.php';
include("db.php");
session_start();

if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "empleado") {
    exit("Acceso no autorizado");
}

$usuario = $_SESSION["usuario"];
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// Obtener ID del usuario
$res = mysqli_query($conn, "SELECT id FROM usuarios WHERE usuario = '$usuario' LIMIT 1");
$usuario_id = mysqli_fetch_assoc($res)['id'];

// Fechas del mes
$inicio = "$anio-$mes-01";
$fin = date("Y-m-t", strtotime($inicio));

// Consultar registros
$query = "SELECT fecha, hora_entrada, hora_salida, hora_entrada2, hora_salida2, total_horas, incidente
          FROM registros
          WHERE usuario_id = '$usuario_id' AND fecha BETWEEN '$inicio' AND '$fin'
          ORDER BY fecha ASC";
$result = mysqli_query($conn, $query);

$html = "<h2 style='text-align:center;'>Resumen de asistencia - $usuario</h2>";
$html .= "<p><strong>Mes:</strong> " . strftime("%B %Y", strtotime($inicio)) . "</p>";
$html .= "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse: collapse; font-size:12px;'>
            <thead>
                <tr style='background-color:#f2f2f2;'>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Entrada 2</th>
                    <th>Salida 2</th>
                    <th>Total Horas</th>
                    <th>Incidente</th>
                </tr>
            </thead>
            <tbody>";

$totalMes = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $totalMes += floatval($row["total_horas"]);
    $html .= "<tr>
                <td>{$row['fecha']}</td>
                <td>{$row['hora_entrada']}</td>
                <td>{$row['hora_salida']}</td>
                <td>{$row['hora_entrada2']}</td>
                <td>{$row['hora_salida2']}</td>
                <td>{$row['total_horas']}</td>
                <td>{$row['incidente']}</td>
            </tr>";
}

$html .= "</tbody></table>";
$html .= "<p><strong>Total de horas trabajadas en el mes:</strong> " . number_format($totalMes, 2) . " h</p>";

// Firmas
$html .= "<br><br><br>
<table style='width:100%; margin-top: 50px; font-size:14px;'>
    <tr>
        <td style='width:50%; text-align:center;'>
            ___________________________<br>
            Firma del empleado
        </td>
        <td style='width:50%; text-align:center;'>
            ___________________________<br>
            Firma del supervisor
        </td>
    </tr>
</table>";

// Crear PDF
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->WriteHTML($html);
$mpdf->Output("resumen_$mes-$anio.pdf", "D");
?>
