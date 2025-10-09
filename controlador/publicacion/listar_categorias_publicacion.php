<?php
include '../../configuracion/conexion.php';

$id_estudiante = $_GET['id_estudiante'];

$query = "SELECT e.id_emprendimiento, c.id_categoria, c.nom_categoria, c.img_categoria
          FROM emprendimiento e
          INNER JOIN categoria c ON e.id_categoria = c.id_categoria WHERE e.id_estudiante='$id_estudiante'";

$result = $con->query($query);
$categorias = array();

while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode($categorias);
?>

