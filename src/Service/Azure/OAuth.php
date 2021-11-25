<?php

namespace App\Service\Azure;

class OAuth
{
    const OAUTH_TENANTID = 'd181a839-8e50-4309-8bc2-d70d12a9b97a';
    const OAUTH_CLIENTID = '5b850fa7-fa29-4703-847a-e0b4602c2d8d';
    const OAUTH_LOGOUT = 'https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0';
    const OAUTH_SCOPE = 'openid%20offline_access%20profile%20user.read';
    const OAUTH_METHOD = 'secret';
    const OAUTH_SECRET = 'vr_7Q~AvqMzj9aleoFTv4dmnHRNkuN0YXrIC2';
    const OAUTH_AUTH_CERTFILE = '/path/to/certificate.crt';
    const OAUTH_AUTH_KEYFILE = '/path/to/privatekey.pem';
    const REDIRECT_URL = 'https://127.0.0.1:8000/azure';

    function generateRequest($code, $codeVerifier) {
        $data = 'grant_type=authorization_code'.
            '&client_id=' . OAuth::OAUTH_CLIENTID .
            '&redirect_uri=' . urlencode(OAuth::REDIRECT_URL . '/oauth') .
            '&code=' . $code .
            '&code_verifier=' . $codeVerifier;
        if (self::OAUTH_METHOD == 'certificate') {
            // Use the certificate specified
            //https://docs.microsoft.com/en-us/azure/active-directory/develop/active-directory-certificate-credentials
            $cert = file_get_contents(self::OAUTH_AUTH_CERTFILE);
            $certKey = openssl_pkey_get_private(file_get_contents(self::OAUTH_AUTH_KEYFILE));
            $certHash = openssl_x509_fingerprint($cert);
            $certHash = base64_encode(hex2bin($certHash));
            $caHeader = json_encode(array('alg' => 'RS256', 'typ' => 'JWT', 'x5t' => $certHash));
            $caPayload = json_encode(array('aud' => 'https://login.microsoftonline.com/' . self::OAUTH_TENANTID . '/v2.0',
                'exp' => date('U', strtotime('+10 minute')),
                'iss' => self::OAUTH_CLIENTID,
                'jti' => $this->uuid(),
                'nbf' => date('U'),
                'sub' => self::OAUTH_CLIENTID));
            $caSignature = '';

            $caData = $this->base64UrlEncode($caHeader) . '.' . $this->base64UrlEncode($caPayload);
            openssl_sign($caData, $caSignature, $certKey, OPENSSL_ALGO_SHA256);
            $caSignature = $this->base64UrlEncode($caSignature);
            $clientAssertion = $caData . '.' . $caSignature;
            return $data . '&client_assertion=' . $clientAssertion . '&client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
        } else {
            // Use the client secret instead
            return $data . '&client_secret=' . urlencode(self::OAUTH_SECRET);
        }

    }

    function postRequest($endpoint, $data) {
        $ch = curl_init('https://login.microsoftonline.com/' . self::OAUTH_TENANTID . '/oauth2/v2.0/' . $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($cError = curl_error($ch)) {
            echo $this->errorMessage($cError);
            die('post request error');
        }
        curl_close($ch);
        return $response;

    }

    function base64UrlEncode($toEncode) {
        return str_replace('=', '', strtr(base64_encode($toEncode), '+/', '-_'));
    }


    function uuid() {
        //uuid function is not my code, but unsure who the original author is. KN
        //uuid version 4
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

}