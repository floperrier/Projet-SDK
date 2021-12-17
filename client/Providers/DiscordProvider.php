<?php

class DiscordProvider extends OauthProvider
{

    protected $base_url = "https://discord.com/api/oauth2/authorize";
    protected $access_token_url = "https://discord.com/api/oauth2/token";
    protected $user_endpoint = "https://discord.com/api/users/@me";
    protected $scope = "identify";

    public function generateAuthLink()
    {
        $authLink = $this->getAuthUrl();
        echo "<a style='background-color: 5865F2;' class='inline-block bg-red-500 box-border text-white rounded px-4 py-2' href='{$authLink}'><i class='fab fa-discord mr-2'></i>Se connecter via Discord</a><br>";
    }
}
