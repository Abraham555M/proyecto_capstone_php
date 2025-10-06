<?php
    // Recibir parámetros desde el POST
    $idEstudiante       = $_POST['id_estudiante'];
    $idCategoria        = $_POST['id_categoria'];
    $nomEmprendimiento  = $_POST['nom_emprendimiento'];
    $desEmprendimiento  = $_POST['des_emprendimiento'];

    // Si recibes las imágenes como archivos (multipart/form-data)
    $imgPorEmprendimiento = null;
    $imgPerEmprendimiento = null;

    // Carpeta donde guardar las imágenes
    $uploadDir = "../../uploads/emprendimientos/";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['img_por_emprendimiento'])) {
        $imgName = uniqid("por_") . "_" . basename($_FILES['img_por_emprendimiento']['name']);
        $targetFile = $uploadDir . $imgName;

        if (move_uploaded_file($_FILES['img_por_emprendimiento']['tmp_name'], $targetFile)) {
            $imgPorEmprendimiento = "uploads/emprendimientos/" . $imgName;
        }
    }

    if (isset($_FILES['img_per_emprendimiento'])) {
        $imgName = uniqid("per_") . "_" . basename($_FILES['img_per_emprendimiento']['name']);
        $targetFile = $uploadDir . $imgName;

        if (move_uploaded_file($_FILES['img_per_emprendimiento']['tmp_name'], $targetFile)) {
            $imgPerEmprendimiento = "uploads/emprendimientos/" . $imgName;
        }
    }

    // Conexión a BD
    require_once("../../configuracion/conexion.php");
    require_once("../../modelo/emprendimiento/emprendimiento.php");

    // Guardar en BD
    $rpta = agregarEmprendimiento(
        $idEstudiante,
        $idCategoria,
        $nomEmprendimiento,
        $desEmprendimiento,
        $imgPorEmprendimiento,
        $imgPerEmprendimiento
    );

    echo json_encode($rpta);
?>