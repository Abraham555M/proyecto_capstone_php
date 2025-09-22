<?php 
    require_once("../../modelo/categoria/categoria.php");

    $rpta = listarCategoria();
    echo json_encode($rpta);
?>