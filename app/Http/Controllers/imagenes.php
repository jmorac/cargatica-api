<?php

//include_once(DIR_INCLUDES."func.php");

class imagenes {

   public $info=array(
    'idcliente'=>"",
   'idWH'=>"",
   'idimg'=>'',
   'nombre'=>'',
   'descripcion'=>'',
   'fileext'=>'',
   'fecha'=>0,
   'tipo'=>0,
   'deleteddate'=>0,
   'deletedby'=>0,
        'paginas'=>0
   );

   private $tbnm='CT_images';
    private $tbnm2='CT_Productos';

      function load($idcliente,$idimg,$nombre,$descripcion,$fileext,$idWH="",$tipo,$paginas=0) {
        global $db;
        global $indexes;
        $errors = array();
        $fecha=time();
       if (strlen($descripcion)<2) {
            $errors[] = "la descripcion debe ser superior a 2";
        }

       if(sizeof($errors)==0){
            $data = array(
                'idcliente' => $idcliente,
                'idimg' => $idimg,
                'nombre'=>$nombre,
                'descripcion'=>$descripcion,
                'fileext'=>strtolower($fileext),
                'fecha'=>$fecha,
                 'idWH'=>$idWH,
                'tipo'=>$tipo,
                'paginas'=>$paginas
            );

            $result = $db->insert($this->tbnm,$data);

            if($result){
                $this->info=(array_intersect_key($data,$this->info));
                 //if($tipo==3){
                    $data= array( 'factura_cliente'=>$descripcion   );
                    $result = $db->update($this->tbnm2,$data,'id='.$idWH);
                 //}


                return true;
            }
            else
              $errors[] = "Error creando Imagen ";
        }
        return $errors;
      }

      function get_by_idcliente($idcliente){
       global $db;
       $result = $info;
	   if($idcliente<>""){
        $SQL="SELECT * FROM `".$this->tbnm."` where idcliente='$idcliente' order by fecha desc";
       $result = $db->query($SQL);
	   }
       $tamano=sizeof($result);
       // echo "sql $SQL tamano".$tamano;
        return $result;

     }

    function get_by_idWH($idWH){
       global $db;
       $result = "";
	   if($idWH<>""){
        $SQL="SELECT * FROM `".$this->tbnm."` where idWH='$idWH' and deletedate=0 order by fecha desc";
       $result = $db->query($SQL);
	   }
       $tamano=sizeof($result);
       // echo "sql $SQL tamano".$tamano;
        return $result;

     }

	  function updatecantidad($idimg,$paginas){
		global $db;
		$result = array();

			$SQL2="update ".$this->tbnm." set   paginas=".$paginas."  where idimg=".$idimg." ";

			$result2 = $db->execute($SQL2);


        return $result;
     }




      function del($idimg){
       global $db;
       $result = array();
        //$SQL="DELETE FROM `".$this->tbnm."` where idimg='$idimg' ";

       $result = $db->delete($this->tbnm,"idimg='$idimg'");

        return $result;

     }


     function getbyid($idimg){
       global $db;
       $result = array();

       $SQL="SELECT * FROM `".$this->tbnm."` where  idimg='$idimg'  ";
//       echo $SQL;
       $result = $db->query($SQL);
       $tamano=sizeof($result);
       // echo "sql $SQL tamano".$tamano;
       // echo "resultado:";
      // print_r($result);
       if ($tamano>0){
         if (is_array($result[0])){
          $this->info=(array_intersect_key($result[0][$this->tbnm],$this->info));
           return true;
         } else return false;
        }
           else
              return false;
     }

}




?>
