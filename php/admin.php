<?php
include("db.php");
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin') {
    echo "⛔ Acceso denegado. Solo para administradores.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="/Style/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light p-4">

<div class="container">

    <!--Menú desplegable arriba a la derecha con tres botones -->
    <div class="d-flex justify-content-end mb-3">
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Opciones Admin
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <!-- Botón Registrar -->
                <li class="px-3 py-2">
                    <form action="registrar_vacaciones.php" method="get">
                        <button type="submit" class="btn btn-warning w-100">Registrar Vacaciones</button>
                    </form>
                </li>
                 <li class="px-3 py-2">
                    <form action="registro.php" method="get">
                        <button type="submit" class="btn btn-warning w-100">Registrar Empleados</button>
                    </form>
                </li>
                <!-- Botón Ver Días Trabajados -->
                <li class="px-3 py-2">
                    <form action="estadisticas.php" method="get">
                        <button type="submit" class="btn btn-info w-100">Ver días trabajados</button>
                    </form>
                </li>

                <!-- Botón Cerrar Sesión -->
                <li class="px-3 py-2">
                    <form action="index.php" method="post">
                        <button type="submit" class="btn btn-danger w-100">Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <h2 class="mb-4">📋 Lista de registros de asistencia</h2>

    <div class="table-wrapper">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
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
                $hoy = date('Y-m-d');

                $query = "SELECT 
                            registros.id, 
                            usuarios.usuario, 
                            registros.fecha, 
                            registros.hora_entrada, 
                            registros.hora_salida, 
                            registros.hora_entrada2, 
                            registros.hora_salida2, 
                            registros.incidente, 
                            registros.total_horas
                          FROM registros 
                          JOIN usuarios ON registros.usuario_id = usuarios.id 
                          WHERE registros.fecha = '$hoy'
                            AND (registros.hora_entrada IS NOT NULL OR registros.hora_entrada2 IS NOT NULL)
                          ORDER BY registros.hora_entrada ASC";

                $result = mysqli_query($conn, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['usuario']}</td>
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
    </div>

</div>
</body>
</html>
