<?php
require_once("../../modelo/comentario/comentario.php");

// 🚨 CORRECCIÓN: Incluir el autoloader de Composer al inicio
require_once('../../vendor/autoload.php'); 

$idComentario = $_POST['idComentario'] ?? null; 
$idEstudiante = $_POST['idEstudiante'] ?? null; 

// Validar datos
if (empty($idComentario) || empty($idEstudiante)) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

require_once("../../modelo/comentario/comentario.php");
$rpta = registrarComentarioLike($idComentario, $idEstudiante);

header('Content-Type: application/json');
echo json_encode($rpta);
?>