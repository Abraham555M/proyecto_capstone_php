<?php
    $idComentario = $_POST['idComentario'];  
    $idEstudiante = $_POST['idEstudiante'];  

    require_once("../../modelo/comentario/comentario.php");
    $rpta = registrarComentarioLike($idComentario, $idEstudiante);
    echo json_encode($rpta);
?>

