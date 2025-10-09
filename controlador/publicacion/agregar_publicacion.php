<?php
include("../../configuracion/conexion.php");

$response = array("success" => false, "message" => "");

// Validar campos obligatorios
if (isset($_POST['id_emprendimiento'], $_POST['id_tipo_publicacion'], $_POST['tit_publicacion'], $_POST['con_publicacion'], $_POST['est_publicacion'])) {
    $id_emprendimiento = $_POST['id_emprendimiento'];
    $id_tipo_publicacion = $_POST['id_tipo_publicacion'];
    $titulo = $_POST['tit_publicacion'];
    $contenido = $_POST['con_publicacion'];
    $estado = $_POST['est_publicacion'];
    $fecha = date("Y-m-d H:i:s");

    // Imagen: ahora viene como URL desde Firebase
    $img_url = $_POST['img_publicacion'] ?? null;

    // Insertar publicación principal
    $sql = "INSERT INTO publicacion 
            (id_emprendimiento, id_tipo_publicacion, tit_publicacion, con_publicacion, img_publicacion, fch_publicacion, est_publicacion)
            VALUES ('$id_emprendimiento', '$id_tipo_publicacion', '$titulo', '$contenido', '$img_url', '$fecha', '$estado')";

    if (mysqli_query($con, $sql)) {
        $id_publicacion = mysqli_insert_id($con);
        $sql_detalle = "";

        // Inserción según tipo
        switch ($id_tipo_publicacion) {
            case "1": // Producto
                $precio = $_POST['prc_producto'] ?? 0;
                $stock = $_POST['stk_producto'] ?? 0;
                $sql_detalle = "INSERT INTO producto (id_publicacion, prc_producto, stk_producto)
                                VALUES ('$id_publicacion', '$precio', '$stock')";
                break;

            case "3": // Promoción
                $descuento = $_POST['dsc_promocion'] ?? 0;
                $fch_ini = $_POST['fch_ini_promocion'] ?? null;
                $fch_fin = $_POST['fch_fin_promocion'] ?? null;
                $sql_detalle = "INSERT INTO promocion (id_publicacion, dsc_promocion, fch_ini_promocion, fch_fin_promocion)
                                VALUES ('$id_publicacion', '$descuento', '$fch_ini', '$fch_fin')";
                break;

            case "4": // Evento
                $fecha_evento = $_POST['fch_evento'] ?? null;
                $lugar_evento = $_POST['lgr_evento'] ?? null;
                $sql_detalle = "INSERT INTO evento (id_publicacion, fch_evento, lgr_evento)
                                VALUES ('$id_publicacion', '$fecha_evento', '$lugar_evento')";
                break;
        }

        if (!empty($sql_detalle)) {
            if (mysqli_query($con, $sql_detalle)) {
                $response["success"] = true;
                $response["message"] = "Publicación registrada correctamente";
            } else {
                $response["message"] = "Error al insertar detalle: " . mysqli_error($con);
            }
        } else {
            $response["success"] = true;
            $response["message"] = "Publicación registrada (sin detalle)";
        }

    } else {
        $response["message"] = "Error al insertar publicación: " . mysqli_error($con);
    }

} else {
    $response["message"] = "Faltan datos obligatorios";
}

echo json_encode($response);
?>
