<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); 

require_once "../../configuracion/conexion.php";

$idEstudiante = isset($_GET['id_estudiante']) ? intval($_GET['id_estudiante']) : 0;

$categorias = array();

if ($idEstudiante > 0) {
    // Selecciona categorías que NO tienen emprendimiento de este estudiante
    $sql = "SELECT * 
            FROM categoria c
            WHERE NOT EXISTS (
                SELECT 1
                FROM emprendimiento e
                WHERE e.id_categoria = c.id_categoria 
                  AND e.id_estudiante = ?
                  AND e.est_emprendimiento = 1
            )";

    if($stmt = $con->prepare($sql)) {
        $stmt->bind_param("i", $idEstudiante);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $categorias[] = [
                "id" => $row["id_categoria"],
                "nombre" => $row["nom_categoria"],
                "imagen" => $row["img_categoria"]
            ];
        }

        $stmt->close();
    }
}

echo json_encode($categorias, JSON_UNESCAPED_UNICODE);
$con->close();
?>
