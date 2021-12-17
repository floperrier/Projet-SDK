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
        $curl = curl_init();
        if (isset($this->method) && $this->method == 'GET') {
            curl_setopt($curl, CURLOPT_URL, $this->access_token_url . "?" . http_build_query($params));
        } else {
            curl_setopt($curl, CURLOPT_URL, $this->access_token_url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        if (!$response) throw new Exception("Erreur lors de l'obtention du token");

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
        if (!$result) throw new Exception("Erreur lors de l'obtention de l'utilisateur");
        return json_decode($result, true);
    }
}
