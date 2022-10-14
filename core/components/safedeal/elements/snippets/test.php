<?php

$callback_url = 'http://megasdelka.local/assets/components/safedeal/payment/callback.php';

$requestParams = array(
    'id' => 12592007,
    'payment_id' => 1,
    'order_id' => 106,
    'card' => '444466******7777',
    'amount' =>  10605,
    'status' =>  'STATUS_PAID',
    'callback_url' => $callback_url,
    'return_url' => "http://success.com",
    'fail_url' => "http://fail.com",
    'url' => "https://{host}/pay/11",
    'projectId' => "000000595",
    'cardToken' => "4bd61d6c-f529-4cab-a7a2-14e786164a75",
    'created_at' => "2019-08-06 12:18:55",
    'status_time' => "2019-08-06T12:40:25"
);

$request = json_encode($requestParams);
$curlOptions = array(
    CURLOPT_URL => $callback_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($request),
        'charset=UTF-8',
    ],
    CURLOPT_POSTFIELDS => $request,
);

$ch = curl_init();
curl_setopt_array($ch, $curlOptions);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    $result = curl_error($ch);
} else {
    $result = json_decode($response, true);
}
$info = curl_getinfo($ch);
curl_close($ch);

echo $info['http_code'] . PHP_EOL;
echo '*****************************' . PHP_EOL;
var_dump($response);
