<?php

namespace App\Service\Azure;

class Graph {
    function __construct(private AuthService $authService) {}
    function getProfile() {
        $profile = json_decode($this->sendGetRequest('https://graph.microsoft.com/v1.0/me/'));
        return $profile;
    }
    function getPhotoSrc() {
        $this->authService->login();
        //Photo is a bit different, we need to request the image data which will include content type, size etc, then request the image
        $photoType = json_decode($this->sendGetRequest('https://graph.microsoft.com/v1.0/me/photo/'));
        $photo = $this->sendGetRequest('https://graph.microsoft.com/v1.0/me/photo/%24value');
        return isset($photoType)
            ? 'data:' . $photoType->{'@odata.mediaContentType'} . ';base64,' . base64_encode($photo)
            : 'https://localhost:8000/img/default-avatar.png';
    }

    function sendGetRequest($URL, $ContentType = 'application/json') {
        $this->authService->login();
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->authService->token, 'Content-Type: ' . $ContentType));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }
}