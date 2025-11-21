<?php
header('Content-Type: application/json');

include("../../configuracion/conexion.php");
include("../../modelo/publicacion/publicacion.php");

$response = ["success" => false, "message" => "Error desconocido"];

try {

    if (!isset($_POST['id_publicacion'])) {
        echo json_encode(["success" => false, "message" => "Falta el id_publicacion"]);
        exit;
    }

    // 📦 Datos base
    $id_publicacion = $_POST['id_publicacion'];
    $id_tipo_publicacion = $_POST['id_tipo_publicacion'];
    $tit_publicacion = $_POST['tit_publicacion'];
    $con_publicacion = $_POST['con_publicacion'];
    $img_publicacion = $_POST['img_publicacion'];

    // 🧩 Actualizar tabla principal
    $ok = actualizarPublicacion(
        $con,
        $id_tipo_publicacion,
        $tit_publicacion,
        $con_publicacion,
        $img_publicacion,
        $id_publicacion
    );

    if (!$ok) {
        echo json_encode(["success" => false, "message" => "No se pudo actualizar la publicación"]);
        exit;
    }

    // 🧩 Actualizar detalle según tipo
    switch ($id_tipo_publicacion) {

        case "1": // PRODUCTO
            actualizarProducto(
                $con,
                $_POST['prc_producto'] ?? null,
                $_POST['stk_producto'] ?? null,
                $id_publicacion
            );
            break;

        case "3": // EVENTO
            actualizarEvento(
                $con,
                $_POST['fch_evento'] ?? null,
                $_POST['lgr_evento'] ?? null,
                $id_publicacion
            );
            break;

        case "2": // PROMOCIÓN
            actualizarPromocion(
                $con,
                $_POST['dsc_promocion'] ?? null,
                $_POST['fch_ini_promocion'] ?? null,
                $_POST['fch_fin_promocion'] ?? null,
                $id_publicacion
            );
            break;
    }

    $response = ["success" => true, "message" => "Publicación actualizada correctamente"];

} catch (Exception $e) {
    $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
}

echo json_encode($response);
