<?php

class GithubProvider extends OauthProvider
{

    protected $base_url = "https://github.com/login/oauth/authorize";
    protected $access_token_url = "https://github.com/login/oauth/access_token";
    protected $user_endpoint = "https://api.github.com/user";
    protected $scope = "user";

    public function generateAuthLink()
    {
        $authLink = $this->getAuthUrl();
        echo "<a style='background-color: 171515;' class='inline-block bg-red-500 box-border text-white rounded px-4 py-2' href='{$authLink}'><i class='fab fa-github mr-2'></i>Se connecter via Github</a><br>";

    }
}