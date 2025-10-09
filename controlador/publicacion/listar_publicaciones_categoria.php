<?php
include '../../configuracion/conexion.php';

$id_estudiante = $_GET['id_estudiante'];
$id_categoria = $_GET['id_categoria'];
$id_emprendimiento = $_GET['id_emprendimiento'];

$query = "SELECT p.id_publicacion AS id,
                 p.tit_publicacion AS titulo,
                 p.con_publicacion AS descripcion,
                 p.img_publicacion AS imagen_url
          FROM publicacion p
          INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
          WHERE e.id_estudiante = '$id_estudiante'
          AND e.id_categoria = '$id_categoria'
          AND p.id_emprendimiento = '$id_emprendimiento'";

$result = $con->query($query);

$publicaciones = array();

while ($row = $result->fetch_assoc()) {
    if (strpos($row['imagen_url'], 'http') === false) {
        $row['imagen_url'] = "uploads/" . $row['imagen_url'];
    }
    $publicaciones[] = $row;
}

echo json_encode($publicaciones);
?>