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

// 3. Recibir URLs de Firebase (en vez de archivos)
$imgPorEmprendimiento = isset($_POST['img_por_emprendimiento']) ? trim($_POST['img_por_emprendimiento']) : "";
$imgPerEmprendimiento = isset($_POST['img_per_emprendimiento']) ? trim($_POST['img_per_emprendimiento']) : "";

// 4. Conexión a BD y modelo
require_once("../../configuracion/conexion.php");
require_once("../../modelo/emprendimiento/emprendimiento.php");

// 5. Insertar en BD
$rpta = agregarEmprendimiento(
    $idEstudiante,
    $idCategoria,
    $nomEmprendimiento,
    $desEmprendimiento,
    $imgPorEmprendimiento,
    $imgPerEmprendimiento
);

// 6. Respuesta en JSON
echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
