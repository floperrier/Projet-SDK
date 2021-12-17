<?php

session_start();

require('Providers/OauthProvider.php');
require('Providers/CustomProvider.php');
require('Providers/DiscordProvider.php');
require('Providers/FacebookProvider.php');
require('Providers/GoogleProvider.php');

function login()
{
    echo "<script src='https://cdn.tailwindcss.com'></script>";
    echo "<script src='https://kit.fontawesome.com/60555e394f.js' crossorigin='anonymous'></script>";

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

        // Discord Auth Link
        $discord = new DiscordProvider([
            'client_id' => '639431807225430027',
            'redirect_uri' => 'https://localhost/redirect_discord',
            'scope' => 'identify',
            'state' => $_SESSION['state']
        ]);
        echo $discord->generateAuthLink();

        // Facebook Auth Link
        $facebook = new FacebookProvider([
            'client_id' => '6092917870781576',
            'redirect_uri' => 'https://localhost/redirect_facebook',
            'scope' => 'public_profile',
            'state' => $_SESSION['state']
        ]);
        echo $facebook->generateAuthLink();

        // Google Auth Link
        $google = new GoogleProvider([
            'client_id' => '240265661795-7gvnmg5tla8jt01ftf4ess1rhea4htth.apps.googleusercontent.com',
            'redirect_uri' => 'https://localhost/redirect_google',
            'scope' => 'openid email',
            'state' => $_SESSION['state']
        ]);
        echo $google->generateAuthLink();

        echo "<form class='my-4 w-40' method='POST'>";
        echo "<input placeholder='Username' class='inline-block w-full px-4 py-2 mt-2 text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-500 focus:outline-none focus:ring' name='username'/>";
        echo "<input placeholder='Password' class='inline-block w-full px-4 py-2 mt-2 text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-500 focus:outline-none focus:ring' name='password'/>";
        echo "<input type='submit' value='submit' class='mt-2 px-4 py-2 font-medium tracking-wide text-white capitalize transition-colors duration-200 transform bg-blue-600 rounded-md hover:bg-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-80'/>";
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
        $user = $provider->getUser($token);
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
        case '/redirect_facebook':
            handleSuccess(new FacebookProvider([
                "client_id" => "6092917870781576",
                "client_secret" => "b980ef18b3d4922747b0ce29a886af04",
                "redirect_uri" => "https://localhost/redirect_facebook",
            ]));
            break;
        case '/redirect_discord':
            handleSuccess(new DiscordProvider([
                "client_id" => "639431807225430027",
                "client_secret" => "a-0LTqNnnFz01Zjm8bAmv0kZpqXNpVhK",
                "redirect_uri" => "https://localhost/redirect_discord",
            ]));
            break;
        case '/redirect_google':
            handleSuccess(new GoogleProvider([
                "client_id" => "240265661795-7gvnmg5tla8jt01ftf4ess1rhea4htth.apps.googleusercontent.com",
                "client_secret" => "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",
                "redirect_uri" => "https://localhost/redirect_google",
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
