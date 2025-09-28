<?php

namespace App\Util\CCProccesors;


class Bac
{

    private $response = "";

    private function estaAprobada(){
        $pos = strrpos($this->response, 'Aprobada');
        return ($pos !== false);
    }

    private function sendData($url,$fields){
        $hdrs = [];
        foreach ($fields as $k => $v) {
            $hdrs[] = $k . ': ' . $v;
        }

        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        $fields_string = rtrim($fields_string, '&');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $resultCURL = curl_exec($ch);
        if (strlen($resultCURL) == 0) {
            $resultCURL = curl_error($ch);
        }

        curl_close($ch);

        $this->response=$resultCURL;
    }

    public function realizar_debito_tarjeta($transID, $UNIQUEID, $Tarjeta, $ccexp, $cvv, $MontoTotal)
    {

        $ret = ["error" => 1, "msg" => '', "ret" => 'sin enviar', "sent" => ''];

        $url = env('BAC_URL');
        $callBack = env('BAC_CALL_BACK');
        $key = env('BAC_KEY');
        $keyid = env('BAC_KEY_ID');
        $time = time();
        $fields = [
            'time' => $time,
            'hash' => md5('' . $UNIQUEID . '|' . $MontoTotal . '|' . $time . '|' . $key),
            'ccnumber' => $Tarjeta,
            'ccexp' => $ccexp,
            'amount' => $MontoTotal,
            'type' => 'sale',
            'orderid' => $UNIQUEID,
            'key_id' => $keyid,
            'cvv' => $cvv,
            'redirect' => urlencode($callBack)];

        $ret['sent'] = json_encode($fields) ;
        $this->sendData($url,$fields);
        $ret['ret'] =  $this->response;

        if($this->estaAprobada()){
            //TO DO: update transaction
            $ret['error'] = 0;
            $ret['msg'] = 'Aprobada';
            return $ret;
        }

        $ret['error'] = 2;
        $ret['msg'] = 'Error';
        return $ret;
    }


}

