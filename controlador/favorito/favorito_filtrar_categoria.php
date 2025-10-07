<?php
require_once("../../modelo/favorito/favorito.php");

$idEstudiante = isset($_GET['idEstudiante']) ? intval($_GET['idEstudiante']) : 0;
$idCategoria = isset($_GET['idCategoria']) ? intval($_GET['idCategoria']) : 0;

$rpta = filtrarFavoritosPorCategoria($idEstudiante, $idCategoria);
echo json_encode($rpta);
?>
