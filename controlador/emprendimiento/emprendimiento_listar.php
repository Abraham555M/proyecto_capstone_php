<?php
require_once("../../modelo/emprendimiento/emprendimiento.php");

// No necesitamos parámetros para listar
$rpta = listarEmprendimientos();
echo json_encode($rpta);
?>
