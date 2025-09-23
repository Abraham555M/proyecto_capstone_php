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


?>