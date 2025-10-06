<?php
include("../../configuracion/conexion.php");

$response = array();

if (isset($_POST["id_emprendimiento"]) && isset($_POST["id_tipo_publicacion"])) {
    $idEmprendimiento = $_POST["id_emprendimiento"];
    $idTipo = $_POST["id_tipo_publicacion"];
    $titulo = $_POST["tit_publicacion"];
    $contenido = $_POST["con_publicacion"];
    $estado = $_POST["est_publicacion"];

    $imgRuta = "";

    // 📌 Procesar imagen si existe
    if (isset($_FILES["img_publicacion"])) {
        $targetDir = "../../uploads/"; // asegúrate que exista y tenga permisos
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["img_publicacion"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["img_publicacion"]["tmp_name"], $targetFilePath)) {
            $imgRuta = "uploads/" . $fileName; // ruta relativa para la BD
        }
    }

    $sql = "INSERT INTO publicacion (id_emprendimiento, id_tipo_publicacion, tit_publicacion, con_publicacion, img_publicacion, est_publicacion) 
            VALUES ('$idEmprendimiento','$idTipo','$titulo','$contenido','$imgRuta','$estado')";
    if (mysqli_query($con, $sql)) {
        $response["success"] = true;
    } else {
        $response["success"] = false;
        $response["message"] = mysqli_error($con);
    }
} else {
    $response["success"] = false;
    $response["message"] = "Parámetros incompletos";
}

echo json_encode($response);
?>
