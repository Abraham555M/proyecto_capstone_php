<?php 
    header("Content-Type: application/json; charset=UTF-8"); // Para que se vea en formato JSON
    require_once("../../modelo/sexo/sexo.php");

    $rpta = listarSexo();
    echo json_encode($rpta);
?>