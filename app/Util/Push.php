<?php
namespace App\Util;

class Push
{

    private $WPURL ='';
    private $WPKEY ='';
    private $DEV = false;
    function __construct($WPURL,$WPKEY,$DEV=false) {
        $this->WPKEY=$WPKEY;
        $this->WPURL=$WPURL;
        $this->DEV  =$DEV;
    }

    public function send($mensaje, $title, $id, $cual)
    {
        $rsts['client_id'] = $cual . $id;
        $rsts['message'] = $mensaje;
        $rsts['title'] = $title;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->WPURL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'accessToken' => $this->WPKEY,
            'targetUserIds' => $rsts['client_id'],
            'notification' => json_encode(['alert' => ['text' => $rsts['message'], 'title' => $rsts['title']]])
        )));
        $rawResponse = curl_exec($ch);
        $resp = "";
        if (curl_errno($ch)) {
            $resp = 'Error: ' . curl_error($ch);
        } else {
            $response = json_decode($rawResponse, true);
            if (isset($response['success']) && $response['success'] === true) {
                $resp = 'Success';
            } else if (isset($response['error']['status'])
                && isset($response['error']['code'])
                && isset($response['error']['message'])) {
                $resp = 'Error ' . $response['error']['status']
                    . ' code ' . $response['error']['code']
                    . ': ' . $response['error']['message'];
            } else {
                $resp = 'Error: ' . $rawResponse;
            }
        }
        curl_close($ch);
        if($this->DEV){
            $resp = $resp.' '.$this->WPURL.' sending:' .print_r($rsts,true).' ';
        }
        return $resp;
    }
}