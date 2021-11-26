<?php

class CustomProvider extends OauthProvider
{

    protected $base_url = "http://localhost:8080/auth";
    protected $access_token_url = "http://server:8080/token";
    protected $user_endpoint = "http://server:8080/me";
    protected $scope = "basic";
    protected $method = "GET";

    public function generateAuthLink()
    {
        $authLink = $this->getAuthUrl();
        echo "<a href='{$authLink}'>Se connecter via ServOAuth</a><br>";
    }
}