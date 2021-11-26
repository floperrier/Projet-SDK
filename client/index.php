<?php

session_start();

require('Providers/OauthProvider.php');
require('Providers/CustomProvider.php');

function login()
{
    if ($_SERVER["REQUEST_METHOD"] === "GET") {

        $_SESSION['state'] = rand();

        // ServOAuth Auth Link
        $custom = new CustomProvider([
            'client_id' => 'client_619f8f8fa8957',
            'redirect_uri' => 'http://localhost:80/redirect_success',
            'scope' => 'basic',
            'state' => $_SESSION['state']
        ]);
        echo $custom->generateAuthLink();

        echo "<form method='POST'>";
        echo "<input name='username'/>";
        echo "<input name='password'/>";
        echo "<input type='submit' value='submit'/>";
        echo "</form>";
    } else {
        $token = getToken(array_merge(["grant_type" => "password"], $_POST));
        $user = getUser($token);
        echo json_encode($user);
    }
}

function handleSuccess(OauthProvider $provider)
{
    try {
        if (empty($_GET['state']) || ($_GET['state'] != $_SESSION['state'])) {
            throw new Exception("State invalide");
        }
        $token = $provider->getAccessToken($_GET);
        if (!$token) throw new Exception("Problème lors de l'obtention du token");
        $user = $provider->getUser($token);
        if (!$user) throw new Exception("Problème lors de l'obtention de l'utilisateur");
        var_dump($user);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

$route = strtok($_SERVER["REQUEST_URI"], "?");
try {
    switch ($route) {
        case "/login":
            login();
            break;
        case '/redirect_success':
            handleSuccess(new CustomProvider([
                "client_id" => "client_619f8f8fa8957",
                "client_secret" => "7edce4cc87fba9b6cccae73a90b425e2",
                "redirect_uri" => "http://localhost:80/redirect_success",
            ]));
            break;         
        default:
            throw new \RuntimeException();
            break;
    }
} catch (\RuntimeException $e) {
    http_response_code(404);
    echo "Not found";
} catch (\Exception $e) {
    http_response_code(500);
    echo $e->getMessage();
}
