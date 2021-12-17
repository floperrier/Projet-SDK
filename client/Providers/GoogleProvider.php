<?php

class GoogleProvider extends OauthProvider
{

    protected $base_url = "https://accounts.google.com/o/oauth2/v2/auth";
    protected $access_token_url = "https://www.googleapis.com/oauth2/v4/token";
    protected $user_endpoint = "https://www.googleapis.com/oauth2/v3/userinfo";
    protected $scope = "openid email";

    public function generateAuthLink()
    {
        $authLink = $this->getAuthUrl();
        echo "<a style='background-color: #e06555' class='inline-block text-white rounded px-4 py-2' href='{$authLink}'><i class='fab fa-google mr-2'></i>Se connecter via Google</a><br>";
    }
}
