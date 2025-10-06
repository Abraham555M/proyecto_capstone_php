<?php
header("Content-Type: application/json; charset=UTF-8");

require_once("../../configuracion/conexion.php"); // tu archivo de conexión a BD

// Recibir parámetro
$idEstudiante = isset($_GET['idEstudiante']) ? intval($_GET['idEstudiante']) : 0;
$baseUrl = isset($_GET['servidorConfig']) ? $_GET['servidorConfig'] : "http://10.0.2.2/proyecto_capstone_php/";

$response = array();

if ($idEstudiante > 0) {
    $sql = "SELECT 
                p.id_publicacion AS id,
                p.tit_publicacion AS titulo,
                p.con_publicacion AS descripcion,
                p.img_publicacion AS imagen_url
            FROM publicacion p
            INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
            WHERE e.id_estudiante = ? 
              AND p.est_publicacion = 1
            ORDER BY p.fch_publicacion DESC";

    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("i", $idEstudiante);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // ⚡ Concatenar la URL completa
            if (!empty($row['imagen_url']) && !preg_match('/^http/', $row['imagen_url'])) {
                $row['imagen_url'] = $baseUrl . $row['imagen_url'];
            }
            $response[] = $row;
        }
        $stmt->close();
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$con->close();
