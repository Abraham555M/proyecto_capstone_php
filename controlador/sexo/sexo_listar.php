<?php 
    require_once("../../modelo/sexo/sexo.php");
    $rpta = listar_sexo();
    echo json_encode($rpta);
?>