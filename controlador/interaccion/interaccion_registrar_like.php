<?php
    require_once('../../vendor/autoload.php');
    $idPublicacion = $_POST['idPublicacion'] ?? null;
    $idEstudiante = $_POST['idEstudiante'] ?? null; 
    if (empty($idPublicacion) || empty($idEstudiante)) {
    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Datos de interacción incompletos."));
    exit;
    }
    require_once("../../modelo/interaccion/interaccion.php");
    $rpta = registrarLike($idPublicacion, $idEstudiante);
    header('Content-Type: application/json');
    echo json_encode($rpta);
?>

