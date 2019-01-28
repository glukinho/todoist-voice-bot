<?php

$oauth_token = 'insert-oauth-token-here';
$post_params = json_encode( [ 'yandexPassportOauthToken' => $oauth_token ] );


$ch = curl_init('https://iam.api.cloud.yandex.net/iam/v1/tokens');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json" ));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1 );
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result_json = curl_exec($ch);
curl_close($ch);

file_put_contents('/var/www/html/todoist-voice-bot/yandex-iam-token.json', $result_json);