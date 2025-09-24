<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // opcional, por si quieres consumir desde cualquier dominio

require_once "../../configuracion/conexion.php";

$sql = "SELECT 
            id_categoria AS id, 
            nom_categoria AS nombre, 
            img_categoria AS imagen
        FROM categoria";

$result = $con->query($sql);

$categorias = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = [
            "id" => $row["id"],
            "nombre" => $row["nombre"],
            "imagen" => $row["imagen"]
        ];
    }
}

echo json_encode($categorias);
$con->close();

?>
