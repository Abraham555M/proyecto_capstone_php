<?php
function listarEmprendimientos() {
    require_once("../../configuracion/conexion.php");

    $data = array("status" => "error", "message" => "No se encontraron emprendimientos", "emprendimientos" => array());

    if ($con) {
        $sql = "SELECT * FROM emprendimiento ORDER BY id_emprendimiento DESC";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $emprendimientos = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $emprendimientos[] = $row;
            }
            $data = array(
                "status" => "success",
                "message" => "Emprendimientos encontrados",
                "emprendimientos" => $emprendimientos
            );
        } else {
            $data = array(
                "status" => "success",
                "message" => "No hay emprendimientos registrados",
                "emprendimientos" => array()
            );
        }
    } else {
        $data = array(
            "status" => "error",
            "message" => "Error en la conexión a la BD",
            "emprendimientos" => array()
        );
    }

    mysqli_close($con);
    return $data;
}
?>
