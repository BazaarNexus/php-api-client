<?php

require(dirname(__DIR__, 2) . '/vendor/autoload.php');

$apiEndPoint = 'https://example.com/api/public/'; // REPLACE example.com by STORE DOMAIN NAME

$accessKey = 'YOUR-ACCESS-KEY';
$accessToken = 'YOUR-ACCESS-TOKEN';

$apiClient = new BazaarNexus\CustomerAPI($accessKey, $accessToken);

$apiClient->setEndPoint($apiEndPoint);

$requestObject = [
    'route' => 'routes|stores|get|stores_orders',
    'reference' => [
        'order_id' => 'ORMVYP7C8BSMRHPRPGUIWK',
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
