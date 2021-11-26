<?php

abstract class OauthProvider
{
    protected $state;
    protected $client_id;
    protected $client_secret;
    protected $redirect_url;
    protected $scope;
    protected $access_token;
    protected $grant_type = "authorization_code";
    protected $method;

    public function __construct(array $params)
    {
        foreach ($params as $key => $param) {
            $this->$key = $param;
        }
    }

    protected function getAuthUrl()
    {
        $params = [
            "response_type" => "code",
            "client_id" => $this->client_id,
            "redirect_uri" => $this->redirect_uri,
            "scope" => $this->scope
        ];
        if ($this->state) $params["state"] = $this->state;
        return $this->base_url . "?" . http_build_query($params);
    }

    public function getAccessToken(array $params)
    {
        ["code" => $this->code] = $params;
        $params = [
            "grant_type" => "authorization_code",
            "code" => $this->code,
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri" => $this->redirect_uri
        ];
        /* $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->access_token_url . "?" . http_build_query($params));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl); */

        $postdata = http_build_query($params);
        if ($this->method == 'POST') {
            $opts = array(
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $postdata
                ]
            );
            $context  = stream_context_create($opts);
            $response = @file_get_contents($this->access_token_url, false, $context);    
        } else {
            $response = file_get_contents($this->access_token_url . "?" . http_build_query($params));
        }
        if (!$response) return false;

        return json_decode($response, true)["access_token"];
    }

    public function getUser($token)
    {
        $context = stream_context_create([
            'http' => [
                "header" => [
                    "Authorization: Bearer " . $token
                ]
            ]
        ]);
        $result = file_get_contents($this->user_endpoint, false, $context);
        return json_decode($result, true);
    }
}
