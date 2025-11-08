<?php
// RUTA: controlador/comentario/comentario_registrar.php

// 🚨 CORRECCIÓN: Incluir el autoloader de Composer al inicio
require_once('../../vendor/autoload.php'); 

$idPublicacion = $_POST['idPublicacion'] ?? null;
$idEstudiante = $_POST['idEstudiante'] ?? null;
$conComentario = $_POST['conComentario'] ?? '';

// Validar datos
if (empty($idPublicacion) || empty($idEstudiante)) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

require_once("../../modelo/comentario/comentario.php");
$rpta = registrarComentario($idPublicacion, $idEstudiante, $conComentario);

header('Content-Type: application/json');
echo json_encode($rpta);
?>