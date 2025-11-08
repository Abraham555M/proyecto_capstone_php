<?php
include('../../configuracion/conexion.php'); // Ajusta la ruta según tu estructura

header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['id_publicacion'])) {
    $id_publicacion = intval($_GET['id_publicacion']);

    // 1️⃣ Primero obtenemos los datos generales de la publicación
    $sql_publicacion = "SELECT 
            p.id_publicacion,
            p.id_emprendimiento,
            p.id_tipo_publicacion,
            p.tit_publicacion,
            p.con_publicacion,
            p.img_publicacion,
            p.fch_publicacion,
            p.est_publicacion,
            tp.nom_tipo_publicacion
        FROM publicacion p
        INNER JOIN tipo_publicacion tp ON p.id_tipo_publicacion = tp.id_tipo_publicacion
        WHERE p.id_publicacion = $id_publicacion";

    $result_publicacion = mysqli_query($con, $sql_publicacion);

    if ($result_publicacion && mysqli_num_rows($result_publicacion) > 0) {
        $publicacion = mysqli_fetch_assoc($result_publicacion);

        $id_tipo = intval($publicacion['id_tipo_publicacion']);
        $datos_extra = [];

        // 2️⃣ Según el tipo de publicación, obtenemos sus datos específicos
        switch ($id_tipo) {
            case 3: // Evento
                $sql_evento = "SELECT 
                                id_evento,
                                id_publicacion,
                                fch_evento,
                                lgr_evento
                               FROM evento
                               WHERE id_publicacion = $id_publicacion";
                $result_evento = mysqli_query($con, $sql_evento);
                if ($result_evento && mysqli_num_rows($result_evento) > 0) {
                    $datos_extra = mysqli_fetch_assoc($result_evento);
                }
                break;

            case 2: // Promoción
                $sql_promocion = "SELECT 
                                    id_promocion,
                                    id_publicacion,
                                    dsc_promocion,
                                    fch_ini_promocion,
                                    fch_fin_promocion
                                  FROM promocion
                                  WHERE id_publicacion = $id_publicacion";
                $result_promocion = mysqli_query($con, $sql_promocion);
                if ($result_promocion && mysqli_num_rows($result_promocion) > 0) {
                    $datos_extra = mysqli_fetch_assoc($result_promocion);
                }
                break;

            case 1: // Producto
                $sql_producto = "SELECT 
                                    id_producto,
                                    id_publicacion,
                                    prc_producto,
                                    stk_producto
                                 FROM producto
                                 WHERE id_publicacion = $id_publicacion";
                $result_producto = mysqli_query($con, $sql_producto);
                if ($result_producto && mysqli_num_rows($result_producto) > 0) {
                    $datos_extra = mysqli_fetch_assoc($result_producto);
                }
                break;

            case 2: // Servicio (sin tabla extra)
                // No requiere datos adicionales
                break;

            default:
                echo json_encode(["status" => "error", "msg" => "Tipo de publicación no reconocido"]);
                exit;
        }

        // 3️⃣ Combinamos todo en un solo JSON
        echo json_encode([
            "status" => "success",
            "data" => [
                "publicacion" => $publicacion,
                "detalles" => $datos_extra
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    } else {
        echo json_encode(["status" => "error", "msg" => "No se encontró la publicación"]);
    }

} else {
    echo json_encode(["status" => "error", "msg" => "Falta el parámetro id_publicacion"]);
}
?>
