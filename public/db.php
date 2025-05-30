<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database  = 'sistema_horario';

$conn = mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die('Error al conectarse a la base de datos'.mysqli_connect_error());
}
?>

