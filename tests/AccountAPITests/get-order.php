<?php

require(dirname(__DIR__, 2) . '/vendor/autoload.php');

$apiKey = 'YOUR-API-KEY';
$apiSecret = 'YOUR-API-SECRET';

$apiClient = new BazaarNexus\AccountAPI($apiKey, $apiSecret);

$requestObject = [
    'route' => 'routes|stores|get|stores_orders',
    'reference' => [
        'order_id' => 'OR1234567890',
    ],
];

try {

    $apiRequest = $apiClient->sendRequest($requestObject);
    if(!$apiClient->checkSuccess($apiRequest)) throw new \Exception($apiClient->getMessage($apiRequest));

    $apiResponse = $apiClient->getData($apiRequest);

    header("Content-Type: application/json");
    echo json_encode($apiResponse);

} catch(\Throwable $e) {

    header("Content-Type: text/plain");
    echo $e->getMessage();
}
