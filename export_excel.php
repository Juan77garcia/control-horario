<?php
include("db.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=registros_horarios.xls");

echo "ID\tUsuario\tFecha\tEntrada\tSalida\n";

$query = "SELECT registros.id, usuarios.usuario, registros.fecha, registros.hora_entrada, registros.hora_salida 
          FROM registros 
          JOIN usuarios ON registros.usuario_id = usuarios.id";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    echo "{$row['id']}\t{$row['usuario']}\t{$row['fecha']}\t{$row['hora_entrada']}\t{$row['hora_salida']}\n";
}
?>

<?php
require('fpdf.php');
include("db.php");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Registros de Asistencia', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10, 'ID', 1);
$pdf->Cell(40, 10, 'Usuario', 1);
$pdf->Cell(30, 10, 'Fecha', 1);
$pdf->Cell(40, 10, 'Entrada', 1);
$pdf->Cell(40, 10, 'Salida', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);

$query = "SELECT registros.id, usuarios.usuario, registros.fecha, registros.hora_entrada, registros.hora_salida 
          FROM registros 
          JOIN usuarios ON registros.usuario_id = usuarios.id";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(10, 10, $row['id'], 1);
    $pdf->Cell(40, 10, $row['usuario'], 1);
    $pdf->Cell(30, 10, $row['fecha'], 1);
    $pdf->Cell(40, 10, $row['hora_entrada'], 1);
    $pdf->Cell(40, 10, $row['hora_salida'], 1);
    $pdf->Ln();
}

$pdf->Output();
?>
