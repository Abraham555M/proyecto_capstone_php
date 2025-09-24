<?php
function listarEmprendimientos($id_estudiante) {
    require_once("../../configuracion/conexion.php");

    $data = array(
        "status" => "error",
        "message" => "No se encontraron emprendimientos",
        "emprendimientos" => array()
    );

    if ($con) {
        // Consulta filtrando por id_estudiante
        $sql = "SELECT * FROM emprendimiento 
                WHERE id_estudiante = ?
                ORDER BY id_emprendimiento DESC";

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id_estudiante);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

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
                    "message" => "No hay emprendimientos registrados para este estudiante",
                    "emprendimientos" => array()
                );
            }
            mysqli_stmt_close($stmt);
        } else {
            $data = array(
                "status" => "error",
                "message" => "Error al preparar la consulta",
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
