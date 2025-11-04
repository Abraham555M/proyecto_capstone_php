<?php
header('Content-Type: application/json');
include("../../configuracion/conexion.php");

$response = ["success" => false, "message" => "Error desconocido"];

try {
    // Validar que se envió el id
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
    $sql = "UPDATE publicacion 
            SET id_tipo_publicacion = ?, tit_publicacion = ?, con_publicacion = ?, img_publicacion = ? 
            WHERE id_publicacion = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssi", $id_tipo_publicacion, $tit_publicacion, $con_publicacion, $img_publicacion, $id_publicacion);
    $stmt->execute();

    if ($stmt->affected_rows >= 0) {
        // ✅ Actualización exitosa en la tabla principal

        // Dependiendo del tipo de publicación, actualizamos su detalle
        switch ($id_tipo_publicacion) {

            // 🟢 PRODUCTO
            case "1": // ID del tipo producto
                $prc_producto = $_POST['prc_producto'] ?? null;
                $stk_producto = $_POST['stk_producto'] ?? null;
                if ($prc_producto !== null && $stk_producto !== null) {
                    $sqlP = "UPDATE producto 
                             SET prc_producto = ?, stk_producto = ?
                             WHERE id_publicacion = ?";
                    $stmtP = $con->prepare($sqlP);
                    $stmtP->bind_param("ssi", $prc_producto, $stk_producto, $id_publicacion);
                    $stmtP->execute();
                }
                break;

            // 🟢 EVENTO
            case "3": // ID del tipo evento
                $fch_evento = $_POST['fch_evento'] ?? null;
                $lgr_evento = $_POST['lgr_evento'] ?? null;
                if ($fch_evento !== null && $lgr_evento !== null) {
                    $sqlE = "UPDATE evento 
                             SET fch_evento = ?, lgr_evento = ?
                             WHERE id_publicacion = ?";
                    $stmtE = $con->prepare($sqlE);
                    $stmtE->bind_param("ssi", $fch_evento, $lgr_evento, $id_publicacion);
                    $stmtE->execute();
                }
                break;

            // 🟢 PROMOCIÓN
            case "2": // ID del tipo promoción
                $dsc_promocion = $_POST['dsc_promocion'] ?? null;
                $fch_ini_promocion = $_POST['fch_ini_promocion'] ?? null;
                $fch_fin_promocion = $_POST['fch_fin_promocion'] ?? null;
                if ($dsc_promocion !== null && $fch_ini_promocion !== null && $fch_fin_promocion !== null) {
                    $sqlPr = "UPDATE promocion 
                              SET dsc_promocion = ?, fch_ini_promocion = ?, fch_fin_promocion = ?
                              WHERE id_publicacion = ?";
                    $stmtPr = $con->prepare($sqlPr);
                    $stmtPr->bind_param("sssi", $dsc_promocion, $fch_ini_promocion, $fch_fin_promocion, $id_publicacion);
                    $stmtPr->execute();
                }
                break;
        }

        $response = ["success" => true, "message" => "Publicación actualizada correctamente"];
    } else {
        $response = ["success" => false, "message" => "No se modificó ninguna fila"];
    }

} catch (Exception $e) {
    $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
}

echo json_encode($response);
?>
