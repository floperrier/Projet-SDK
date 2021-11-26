<?php

$array = [];
for ($i=0; $i< 10; $i++) {
    $array[] = serialize([
        "user_id"=> $i,
        "username" => "user_".$i,
        "password" => "test",
        "lastname" => "last_".$i,
        "firstname" => "first_".$i,
    ]);
}

file_put_contents('./data/user.db', implode("\n", $array));
