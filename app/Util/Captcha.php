<?php

namespace App\Util;

class ReCaptchaResponse {
    public $is_valid;
    public $error;
}

class Captcha
{
    public static function recaptcha_check($privatekey, $remoteIp, $gRecaptchaResponse)
    {
        $response = new ReCaptchaResponse();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::RECAPTCHA_API_SERVER);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'secret' => $privatekey,
            'response' => $gRecaptchaResponse,
            'remoteip' => $remoteIp
        ]);

        // Set timeout (in seconds)
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds

        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if ($resp !== null && isset($resp['success'])) {
            $response->is_valid = $resp['success'];
            if (!$response->is_valid) {
                $response->error = array_key_exists('error-codes', $resp) ? $resp['error-codes'] : [];
            }
        } else {
            // Handle API response error here
            $response->is_valid = false;
            $response->error = ["API response was invalid"];
        }

        return $response;
    }

    // Define class constant for API server
    const RECAPTCHA_API_SERVER = 'https://www.google.com/recaptcha/api/siteverify';
}
