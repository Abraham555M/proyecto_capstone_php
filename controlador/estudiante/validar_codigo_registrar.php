<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once("../../configuracion/conexion.php");

$codigo     = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$correo     = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$nombres    = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
$apePat     = isset($_POST['apePat']) ? trim($_POST['apePat']) : '';
$apeMat     = isset($_POST['apeMat']) ? trim($_POST['apeMat']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
$celular    = isset($_POST['celular']) ? trim($_POST['celular']) : '';
$id_sexo    = isset($_POST['id_sexo']) ? intval($_POST['id_sexo']) : 0;
$id_sede    = isset($_POST['id_sede']) ? intval($_POST['id_sede']) : 0;
$fecha_reg  = date("Y-m-d H:i:s");
$id_tipo_usuario = 1; // tipo por defecto

if (empty($codigo) || empty($correo) || empty($nombres) || empty($apePat) || empty($apeMat) || empty($contrasena)) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit();
}

try {
    // Paso 1: Buscar el registro temporal
    $sql = "SELECT cod_estudiante, cod_expira FROM estudiante WHERE ema_estudiante = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo json_encode(["status" => "error", "msg" => "No se encontró el correo"]);
        exit();
    }

    $codigo_bd = $row['cod_estudiante'];
    $codigo_expira = $row['cod_expira'];

    // Paso 2: Validar código
    $ahora = new DateTime();
    $expira = new DateTime($codigo_expira);

    if ($codigo != $codigo_bd) {
        echo json_encode(["status" => "codigo_invalido", "msg" => "Código incorrecto"]);
        exit();
    }

    if ($ahora > $expira) {
        echo json_encode(["status" => "codigo_expirado", "msg" => "El código ha expirado"]);
        exit();
    }

    // Paso 3: Actualizar registro
    $passwordHash = password_hash($contrasena, PASSWORD_BCRYPT);
    $sqlUpdate = "UPDATE estudiante SET 
        id_sexo = ?, 
        id_sede = ?, 
        id_tipo_usuario = ?, 
        nom_estudiante = ?, 
        ape_pat_estudiante = ?, 
        ape_mat_estudiante = ?, 
        fch_reg_estudiante = ?, 
        est_estudiante = 1,
        tel_estudiante = ?, 
        pas_estudiante = ?
    WHERE ema_estudiante = ?";
    $stmtUp = $con->prepare($sqlUpdate);
    $stmtUp->bind_param(
        "iiisssssss",
        $id_sexo,
        $id_sede,
        $id_tipo_usuario,
        $nombres,
        $apePat,
        $apeMat,
        $fecha_reg,
        $celular,
        $passwordHash,
        $correo
    );
    $stmtUp->execute();

    // Paso 4: Obtener los datos completos del usuario actualizado
    $sqlSelect = "SELECT id_estudiante, nom_estudiante, CONCAT(ape_pat_estudiante, ' ', ape_mat_estudiante) AS apellidos, id_tipo_usuario 
                  FROM estudiante WHERE ema_estudiante = ?";
    $stmtSel = $con->prepare($sqlSelect);
    $stmtSel->bind_param("s", $correo);
    $stmtSel->execute();
    $resultSel = $stmtSel->get_result();
    $user = $resultSel->fetch_assoc();

    echo json_encode([
        "status" => "ok",
        "msg" => "Cuenta creada correctamente",
        "user" => $user
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
}
?>
