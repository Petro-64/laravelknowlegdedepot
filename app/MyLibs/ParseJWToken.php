<?php
namespace App\MyLibs;

use Illuminate\Support\Facades\Config;

class ParseJWToken {
   public static function doParse($jwt){
        if(strlen($jwt) > 20){
            $tokenParts = explode('.', $jwt);
            $header = base64_decode($tokenParts[0]);
            $payload = base64_decode($tokenParts[1]);
            $roleId = json_decode($payload)->{'role_id'};
            $userId = json_decode($payload)->{'user_id'};
            $loginTimestamp = json_decode($payload)->{'login_timestamp'};
            $signatureProvided = $tokenParts[2];
            $base64UrlHeader = base64_encode($header);
            $base64UrlPayload = base64_encode($payload);
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, Config::get('jwt.secret'), true);
            $base64UrlSignature = base64_encode($signature);
            $signatureValid = ($base64UrlSignature === $signatureProvided);
            $delta = time() - $loginTimestamp;
        } else {
            $signatureValid = 'false';
            $roleId = 0;
            $delta = 0;
            $userId = 4;
        }

        return array('role_id' => $roleId, 'signature_valid' => $signatureValid, 'delta' => $delta, 'user_id' => $userId);
   }
}