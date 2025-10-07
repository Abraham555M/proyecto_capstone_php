<?php
require_once("../../modelo/favorito/favorito.php");

$idEstudiante = isset($_GET['idEstudiante']) ? intval($_GET['idEstudiante']) : 0;
$texto = isset($_GET['texto']) ? trim($_GET['texto']) : '';

$rpta = buscarFavoritosPublicacion($idEstudiante, $texto);
echo json_encode($rpta);
?>
