<?php
require_once("../../configuracion/conexion.php"); // tu archivo de conexión a BD

$sql = "SELECT id_tipo_publicacion, nom_tipo_publicacion FROM tipo_publicacion";
$result = $con->query($sql);

$datos = array();

while ($row = $result->fetch_assoc()) {
    $datos[] = $row;
}

echo json_encode($datos);
?>
