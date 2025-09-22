<?php 
    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = listarPublicacionInicio();
    echo json_encode($rpta);
?>