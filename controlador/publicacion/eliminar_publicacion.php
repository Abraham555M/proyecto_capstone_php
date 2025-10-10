<?php
include '../../configuracion/conexion.php';

$id_publicacion = $_POST['id_publicacion'] ?? null;

if ($id_publicacion) {
    $query = "UPDATE publicacion SET est_publicacion = 0 WHERE id_publicacion = '$id_publicacion'";
    if (mysqli_query($con, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => mysqli_error($con)]);
    }
} else {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
}
?>
