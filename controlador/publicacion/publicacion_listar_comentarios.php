<?php 
    $idPublicacion = $_GET['idPublicacion'];  

    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = listarComentariosPublicacion($idPublicacion);
    echo json_encode($rpta);
?>