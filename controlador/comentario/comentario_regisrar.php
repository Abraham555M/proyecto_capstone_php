<?php
    $idPublicacion = $_POST['idPublicacion'];  
    $idEstudiante = $_POST['idEstudiante'];  
    $conComentario = $_POST['conComentario'];  

    require_once("../../modelo/comentario/comentario.php");
    $rpta = RegistrarComentario($idPublicacion, $idEstudiante, $conComentario);
    echo json_encode($rpta);
?>

