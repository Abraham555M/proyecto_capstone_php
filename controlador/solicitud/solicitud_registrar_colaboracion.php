<?php
    $idEstudiante = $_POST['idEstudiante'];  
    $idPublicacion = $_POST['idPublicacion'];  
    $idEmprendimiento = $_POST['idEmprendimiento'];      
    $menSolicitud = $_POST['menSolicitud'];  

    require_once("../../modelo/solicitud/solicitud.php");
    $rpta = registrarColaboracion($idEstudiante, $idPublicacion, $idEmprendimiento, $menSolicitud);

    echo json_encode($rpta);
?>

