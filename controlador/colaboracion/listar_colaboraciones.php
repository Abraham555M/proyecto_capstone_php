<?php
header('Content-Type: application/json');
require_once '../../configuracion/conexion.php';

$response = array();

// ✅ Recibir el id_estudiante del usuario logueado
$id_estudiante = isset($_GET['id_estudiante']) ? intval($_GET['id_estudiante']) : 0;

if ($id_estudiante <= 0) {
    $response['success'] = false;
    $response['message'] = 'ID de estudiante no válido.';
    echo json_encode($response);
    exit;
}

$sql = "
    SELECT 
        c.*, 
        p.id_publicacion AS pub_id,
        p.tit_publicacion AS pub_titulo,
        p.con_publicacion AS pub_descripcion,
        p.img_publicacion AS pub_imagen
    FROM colaboracion c
    INNER JOIN emprendimiento e ON c.id_emprendimiento = e.id_emprendimiento
    INNER JOIN publicacion p ON c.id_publicacion = p.id_publicacion
    WHERE e.id_estudiante = ?
      AND c.est_colaboracion IN (0, 1)
";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $colaboraciones = array();

    while ($row = $result->fetch_assoc()) {
        $colaboraciones[] = array(
            'id_colaboracion' => $row['id_colaboracion'],
            'id_estudiante' => $row['id_estudiante'],
            'id_emprendimiento' => $row['id_emprendimiento'],
            'id_publicacion' => $row['id_publicacion'],
            'men_colaboracion' => $row['men_colaboracion'],
            'fch_colaboracion' => $row['fch_colaboracion'],
            'est_colaboracion' => $row['est_colaboracion'],
            'publicacion' => array(
                'id' => $row['pub_id'],
                'titulo' => $row['pub_titulo'],
                'descripcion' => $row['pub_descripcion'],
                'imagenUrl' => $row['pub_imagen']
            )
        );
    }

    $response['success'] = true;
    $response['data'] = $colaboraciones;
} else {
    $response['success'] = false;
    $response['message'] = 'No hay colaboraciones pendientes o aceptadas.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$con->close();
?>