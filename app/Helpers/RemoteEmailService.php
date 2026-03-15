<?php

namespace App\Helpers;


class RemoteEmailService
{

    // set from env

    protected $token;
    public $Addresses = [];
    public $from = '';
    public $fromEmail = '';
    public $Subject ='';
    public $Message ='';

    public $Body = '';
    public $attachment = '';
    public $altBody = '';
    public $CharSet = '';


    public function __construct()
    {
        $this->token = config('services.email_server_api.token');
    }

    public function load($to_email, $to_name,$subject, $body  ){
        $this->AddAddress($to_email,$to_name);
        $this->SetFrom($this->from);
        $this->fromEmail($this->fromEmail);
        $this->Subject = $subject;
        $this->MsgHTML($body);
    }


    public function AddAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    public function IsHTML($isHtml)
    {
        // $this->isHtml = $isHtml;
    }
    public function MsgHTML($message)
    {
        $this->Message = $message;
    }
    public function SetFrom($from)
    {
        $this->from = $from;
    }



    public function fromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
    }

    public function AddAddress($email,$name = '')
    {
        $address = new \stdClass();
        $address->email = $email;
        $address->name = $name;

        $this->Addresses[] = $address;
    }


    public function addStringAttachment($string, $filename)
    {
        $this->attachment = $string;
    }

    public function Send(){
        $curl = curl_init();
        foreach ($this->Addresses as $address){
            curl_setopt_array($curl, array(
                CURLOPT_URL =>  config('services.email_server_api.url'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('to_email' => $address->email,'to_name' => $address->name,'subject' => $this->Subject,'message' => $this->Message.$this->Body,'attachment_path' => $this->attachment),
                CURLOPT_HTTPHEADER => array(
                    'X-APP-TOKEN: '.$this->token,
                ),
            ));

            $response = curl_exec($curl);
        }
        curl_close($curl);
        return $response;
    }
}
