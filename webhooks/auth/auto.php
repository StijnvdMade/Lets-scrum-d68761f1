<?php

$json = file_get_contents('php://input');
$json_object = json_decode($json);

$code = $_GET['code'];

$token;

$client_id = '870241712243.883925278630';
$secret = '93585553288087852f46cc4a3e1b9a6a';
$url = 'https://4biddenshop.com/ns/auto.php';

error_log($code);

    $response = file_get_contents("https://slack.com/api/oauth.access?client_id={$client_id}&client_secret={$secret}&code={$code}");


    $jss = json_decode($response);
    //echo "<pre>"; var_dump($jss); echo "</pre>";
    $token = $jss->access_token;

    error_log($token);


?>