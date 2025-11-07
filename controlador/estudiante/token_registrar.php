<?php
// RUTA: controlador/estudiante/token_registrar.php

// 1. Obtener los datos POST del cliente (aplicación móvil)
$idEstudiante = isset($_POST['idEstudiante']) ? (int)$_POST['idEstudiante'] : null; 
$tokenFCM = isset($_POST['tokenFCM']) ? $_POST['tokenFCM'] : null; 

// Establecer el encabezado de respuesta como JSON
header('Content-Type: application/json');

if (empty($idEstudiante) || empty($tokenFCM)) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Datos incompletos. Se requiere idEstudiante y tokenFCM."
    ));
    exit;
}

// 2. Incluir el modelo
// 🚨 CORRECCIÓN FINAL: Sube DOS niveles (../../) a la raíz, luego entra a 'modelo'
require_once "../../modelo/estudiante/estudiante.php";

// 3. Llamar a la función del modelo
$rpta = registrarTokenFCM($idEstudiante, $tokenFCM);

// 4. Devolver la respuesta al cliente
echo json_encode($rpta);
?>