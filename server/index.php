<?php

function readDatabase($filename)
{
    if (!file_exists($filename)) {
        throw new \Exception("{$filename} not found");
    }
    $data = file($filename);
    return array_map(fn ($item) => unserialize($item), $data);
}

function writeDatabase($data, $filename)
{
    if (!file_exists($filename)) {
        throw new \Exception("{$filename} not found");
    }
    $db = readDatabase($filename);
    $db[] = $data;
    file_put_contents(
        $filename, 
        implode(
            PHP_EOL, 
            array_map(fn($item) => serialize($item), $db)
        )
    );
}

function findBy($criteria, $filename) {
    $db = readDatabase($filename);
    $result = array_values(
        array_filter($db, function($item) use ($criteria) {
            return count(array_intersect_assoc($item, $criteria)) === count($criteria);
        })
    );
    // <=> $result = array_filter($db, fn($item) => count(array_intersect_assoc($item, $criteria)) === count($criteria));
    return count($result) > 0 ? $result[0] : null;
}

function findApp($criteria) {
    return findBy($criteria, "./data/app.db");
}

function findCode($criteria) {
    return findBy($criteria, "./data/code.db");
}

function findToken($criteria) {
    return findBy($criteria, "./data/token.db");
}

function findUser($criteria) {
    return findBy($criteria, "./data/user.db");
}

function register()
{
    ["name" => $name] = $_POST;
    $db = readDatabase('./data/app.db');
    foreach($db as $app) {
        if ($app['name'] === $name) throw new \Exception("{$name} application already registered");
    }
    $clientId = uniqid("client_");
    $clientSecret = md5($clientId);
    $data = array_merge($_POST, ["client_id" => $clientId, "client_secret" => $clientSecret]);
    writeDatabase($data, './data/app.db');
    http_response_code(201);
    echo json_encode($data);
}

function auth() {
    ["client_id" => $clientId, "scope"=> $scope, "state" => $state, "redirect_uri" => $redirectUri] = $_GET;
    $app = findApp(["client_id" => $clientId, "redirect_success" => $redirectUri]);
    var_dump($app);
    if(!$app) throw new \Exception("Client ID '{$clientId}' not found");
    $codeEntity = findCode(["client_id" => $clientId]);
    var_dump($codeEntity);
    if ($codeEntity && $codeEntity['expiresIn'] > (new \DateTimeImmutable())->getTimestamp()) {
        header("Location: {$app['redirect_success']}?code={$codeEntity['code']}&state={$state}");
        return;
    }
    echo "<p><a href=\"{$app['url']}\">{$app['name']}</a></p>";
    echo "<p>{$scope}</p>";
    echo "<a href=\"http://localhost:8080/auth-success?client_id={$clientId}&state={$state}\">Oui</a>";
    echo "<a href=\"http://localhost:8080/auth-cancel?client_id={$clientId}&state={$state}\">Non</a>";
};

function handleAuth($success) {
    ['client_id' => $clientId, "state" => $state] = $_GET;
    $app = findApp(['client_id' => $clientId]);
    if(!$app) throw new \Exception("Client ID '{$clientId}' not found");

    if ($success) {
        $code = uniqid("code_", true);
        $codeEntity = [
            "code" => $code,
            "client_id" => $clientId,
            "expiresIn" => (new \DateTimeImmutable())->modify('+5 minutes')->getTimestamp(),
            "user_id" => 0
        ];
        writeDatabase($codeEntity, './data/code.db');
        $url = $app['redirect_success']. "?" . http_build_query(["code" => $code, "state" => $state]);
    } else {
        $url = $app['redirect_cancel']."?state={$state}";
    }

    header("Location: {$url}");
}

function handleAuthCode(string $clientId): ?string {
    ["code"=> $code] = $_GET;
    $codeEntity = findCode(["client_id" => $clientId, "code"=> $code]);
    if (!$codeEntity) throw new \Exception("Code '{$code}' not found");
    if ($codeEntity['expiresIn'] < (new \DateTimeImmutable())->getTimestamp()) throw new \Exception("Code '{$code}' expired");
    return $codeEntity['user_id'];
}

function handlePassword(): ?string {
    ["username"=> $username, "password" => $password] = $_GET;
    $userEntity = findUser(["username" => $username, "password" => $password]);
    if (!$userEntity) throw new \Exception("User '{$username}' not found");
    return $userEntity['user_id'];
}

function token(){
    if ((!isset($_GET['grant_type'], $_GET['client_id'], $_GET['client_secret']))) {
        throw new \Exception("Paramètres invalides");
    }
    ["grant_type" => $grantType, "client_id"=> $clientId, "client_secret" => $clientSecret] = $_GET;
    $app = findApp(['client_id' => $clientId, 'client_secret' => $clientSecret]);
    if (!$app) throw new \Exception("Client ID '{$clientId}' not found");
    $userId = match($grantType) {
        "authorization_code" => handleAuthCode($clientId),
        "password" => handlePassword(),
        'client_credentials' => null,
        _ => throw new \Exception("Grant type '{$grantType}' not supported")
    };
    $token = uniqid('token_', true);
    $tokenEntity = [
        "client_id" => $clientId,
        "user_id" => $userId,
        "token" => $token,
        "expiresIn" => (new \DateTimeImmutable())->modify('+30 days')->getTimestamp()
    ];
    writeDatabase($tokenEntity, "./data/token.db");
    echo json_encode([
        'access_token' => $token,
        'expiresIn' => $tokenEntity['expiresIn']
    ]);
};

function extractToken() {
    $header = getallheaders()['Authorization'] ?? getallheaders()['authorization'];
    [, $token] = explode(' ', $header);
    $tokenEntity = findToken(["token" => $token]);
    if (!$tokenEntity) throw new \Exception("Token '{$token}' not found");
    if ($tokenEntity['expiresIn'] < (new \DateTimeImmutable())->getTimestamp()) throw new \Exception("Token '{$token}' expired");
    return $tokenEntity;
}

function me(){
    $tokenEntity = extractToken();
    if ($tokenEntity['user_id'] === null) throw new \Exception("Must use a user-based token");
    $user = findUser(["user_id" => $tokenEntity['user_id']]);
    echo json_encode($user);
}

function stats(){
    extractToken();
    echo json_encode([
        "totalMovies" => 40
    ]);
}

$route = strtok($_SERVER["REQUEST_URI"], "?");
try {
    switch ($route) {
    case '/register':
        register();
        break;
    case '/auth':
        auth();
        break;
    case '/auth-success':
        handleAuth(true);
        break;
    case '/auth-cancel':
        handleAuth(false);
        break;
    case '/token':
        token();
        break;
    case '/me':
        me();
        break;
    case '/stats':
        me();
        break;
    default:
        throw new \RuntimeException();
    break;
}
} catch(\RuntimeException $e) {
    http_response_code(404);
    echo "Not found";
} catch(\Exception $e) {
    http_response_code(500);
    echo $e->getMessage();
}
