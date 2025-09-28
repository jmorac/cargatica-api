<?php

namespace App\Util;


class Ticas{
    function __construct(){
        //identificacion=110800661
    }

    private function getApellidos($nombre,$tipo){
        if($tipo!=1){
            return;
        }
        $arr= explode(' ',$nombre);
        $ret=new \stdClass();
        $ret->nombre = ucfirst( strtolower($arr[0]));
        if(sizeof($arr) ==4){
            $ret->segundo_nombre = ucfirst( strtolower($arr[1]));
            $ret->apellido = ucfirst( strtolower($arr[2]));
            $ret->segundo_apellido =ucfirst( strtolower($arr[3]));
        }

        if(sizeof($arr) ==3){
            $ret->segundo_nombre ='';
            $ret->apellido =ucfirst( strtolower($arr[1]));
            $ret->segundo_apellido =ucfirst( strtolower($arr[2]));
        }

        if(sizeof($arr) ==2){
            $ret->apellido =ucfirst( strtolower($arr[1]));
        }

        return $ret;

    }

    public function getData(  $numero){
        $curl = curl_init();
        $url='https://api.hacienda.go.cr/fe/ae?identificacion='.trim($numero).'&fbclid=IwAR02FDNelCoBgG1JGdrQnVG2SvYmv2WU1YhX5zdM97ZPamTbayT1iwrNr9g';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_TIMEOUT=>3,
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $obj= json_decode(trim($response));

        $ret=new \stdClass();
        $ret->identificacion=$numero;
        if(!$obj){
            return false;
        }
        $ret->nombre_desglozado=$this->getApellidos($obj->nombre,$obj->tipoIdentificacion);
        $ret->tipoIdentificacion=$obj->tipoIdentificacion?$obj->tipoIdentificacion:'';
        $ret->nombre=$obj->nombre?$obj->nombre:'';
        $ret->situacion_tributaria=$obj->situacion?$obj->situacion:'';
        return $ret;
    }


}

