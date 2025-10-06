<?php
header("Content-Type: application/json; charset=UTF-8");

// 1. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// 2. Recibir parámetros obligatorios
$idEstudiante       = isset($_POST['id_estudiante']) ? intval($_POST['id_estudiante']) : 0;
$idCategoria        = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
$nomEmprendimiento  = isset($_POST['nom_emprendimiento']) ? trim($_POST['nom_emprendimiento']) : "";
$desEmprendimiento  = isset($_POST['des_emprendimiento']) ? trim($_POST['des_emprendimiento']) : "";

if ($idEstudiante <= 0 || $idCategoria <= 0 || empty($nomEmprendimiento)) {
    echo json_encode(["error" => "Faltan datos obligatorios"]);
    exit;
}

// 3. Variables para imágenes
$imgPorEmprendimiento = null;
$imgPerEmprendimiento = null;

// Carpeta donde guardar las imágenes
$uploadDir = "../../uploads/emprendimientos/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 4. Subir imagen de portada
if (isset($_FILES['img_por_emprendimiento']) && $_FILES['img_por_emprendimiento']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['img_por_emprendimiento']['name'], PATHINFO_EXTENSION);
    $imgName = uniqid("por_") . "." . $extension;
    $targetFile = $uploadDir . $imgName;

    if (move_uploaded_file($_FILES['img_por_emprendimiento']['tmp_name'], $targetFile)) {
        $imgPorEmprendimiento = "uploads/emprendimientos/" . $imgName; // ruta relativa que guardarás en BD
    }
}

// 5. Subir imagen de perfil
if (isset($_FILES['img_per_emprendimiento']) && $_FILES['img_per_emprendimiento']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['img_per_emprendimiento']['name'], PATHINFO_EXTENSION);
    $imgName = uniqid("per_") . "." . $extension;
    $targetFile = $uploadDir . $imgName;

    if (move_uploaded_file($_FILES['img_per_emprendimiento']['tmp_name'], $targetFile)) {
        $imgPerEmprendimiento = "uploads/emprendimientos/" . $imgName;
    }
}

// 6. Conexión a BD y modelo
require_once("../../configuracion/conexion.php");
require_once("../../modelo/emprendimiento/emprendimiento.php");

// 7. Insertar en BD
$rpta = agregarEmprendimiento(
    $idEstudiante,
    $idCategoria,
    $nomEmprendimiento,
    $desEmprendimiento,
    $imgPorEmprendimiento,
    $imgPerEmprendimiento
);

// 8. Respuesta en JSON
echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>