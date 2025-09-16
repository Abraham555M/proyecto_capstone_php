<?php 
   function listarSexo(){
      require_once("../../configuracion/conexion.php");
      
      $sql = "SELECT * FROM sexo"; 
      $result = mysqli_query($con, $sql);

      $data = [];
      if ($result) {
         while ($row = mysqli_fetch_assoc($result)) {
               $data[] = $row; // Cada fila en el array
         }
      }

      return $data; // Retorna array listo para json_encode
   }
?>