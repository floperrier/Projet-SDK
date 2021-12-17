<?php

class FacebookProvider extends OauthProvider
{

    protected $base_url = "https://www.facebook.com/v12.0/dialog/oauth";
    protected $access_token_url = "https://graph.facebook.com/v12.0/oauth/access_token";
    protected $user_endpoint = "https://graph.facebook.com/v2.10/me";
    protected $scope = "public_profile";

    public function generateAuthLink()
    {
        $authLink = $this->getAuthUrl();
        echo "<a style='background-color: #5770a6' class='inline-block my-2 text-white rounded px-4 py-2' href='{$authLink}'><i class='fab fa-facebook mr-2'></i>Se connecter via Facebook</a><br>";
    }
}
