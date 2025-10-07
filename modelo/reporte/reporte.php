<?php 
   function listarTiposReporte(){
      require_once("../../configuracion/conexion.php");
      
      $sql = "SELECT * FROM tipo_reporte"; 
      $result = mysqli_query($con, $sql);

      $data = [];
      if ($result) {
         while ($row = mysqli_fetch_assoc($result)) {
               $data[] = $row;
         }
      }

      return $data; 
   }

   function registrarReporte($idEstudiante, $idPublicacion, $idTipoReporte){
        include("../../configuracion/conexion.php"); // conexión en $con

        // Insertar el nuevo reporte
        $sql_insert = "INSERT INTO reporte (id_estudiante, id_publicacion, id_tipo_reporte, est_reporte) 
                    VALUES ('$idEstudiante', '$idPublicacion', '$idTipoReporte', 1)";
        
        if(mysqli_query($con, $sql_insert)){
            return array("status" => "reported");
        } else {
            return array("status" => "error", "message" => mysqli_error($con));
        }
    }

    function registrarReporteComentario($idEstudiante, $idComentario, $idTipoReporte){
    include("../../configuracion/conexion.php"); // $con

    // Validación simple (opcional pero recomendable)
    if (empty($idEstudiante) || empty($idComentario) || empty($idTipoReporte)) {
        return array("status" => "error", "message" => "Faltan parámetros");
    }

    // Sanitizar inputs
    $idEstudiante = mysqli_real_escape_string($con, $idEstudiante);
    $idComentario  = mysqli_real_escape_string($con, $idComentario);
    $idTipoReporte = mysqli_real_escape_string($con, $idTipoReporte);

    // Evitar reportes duplicados (mismo estudiante + mismo comentario con est_reporte = 1)
    $sql_check = "SELECT id_reporte FROM reporte 
                  WHERE id_estudiante = '$idEstudiante' 
                    AND id_comentario = '$idComentario' 
                    AND est_reporte = 1";
    $res_check = mysqli_query($con, $sql_check);
    if ($res_check && mysqli_num_rows($res_check) > 0) {
        return array("status" => "error", "message" => "Ya reportaste este comentario.");
    }

    // Insertar reporte (guardamos id_comentario en lugar de id_publicacion)
    $sql_insert = "INSERT INTO reporte (id_estudiante, id_comentario, id_tipo_reporte, est_reporte)
                   VALUES ('$idEstudiante', '$idComentario', '$idTipoReporte', 1)";

    if (mysqli_query($con, $sql_insert)) {
        return array("status" => "reported");
    } else {
        return array("status" => "error", "message" => mysqli_error($con));
    }
    }

?>