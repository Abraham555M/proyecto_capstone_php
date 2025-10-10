<?php 
    function listarSolicitudesSoporte($idEstudiante){
        require_once("../../configuracion/conexion.php"); // tu conexión MySQLi (variable $con)

        // Verificar que el parámetro sea válido
        if (empty($idEstudiante) || !is_numeric($idEstudiante)) {
            return ["status" => "error", "message" => "ID de estudiante no válido."];
        }

        // Consulta para obtener las solicitudes del estudiante
        $sql = "SELECT id_soporte, id_estudiante, men_soporte, fec_soporte, est_soporte 
                FROM soporte 
                WHERE id_estudiante = ? 
                ORDER BY fec_soporte DESC";

        // Preparar la sentencia
        if ($stmt = $con->prepare($sql)) {
            $stmt->bind_param("i", $idEstudiante);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $solicitudes = [];
            while ($fila = $resultado->fetch_assoc()) {
                $solicitudes[] = $fila;
            }

            $stmt->close();
            $con->close();

            return $solicitudes;
        } else {
            return ["status" => "error", "message" => "Error al preparar la consulta."];
        }
    }
?>
