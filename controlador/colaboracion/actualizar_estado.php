<?php
include('../../configuracion/conexion.php');

$id = $_GET['id_colaboracion'];
$estado = $_GET['estado'];

$sql = "UPDATE colaboracion SET est_colaboracion = $estado WHERE id_colaboracion = $id";

if (mysqli_query($con, $sql)) {
    echo json_encode(["success" => true, "message" => "Estado actualizado"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar"]);
}
?>