<?php
namespace BazaarNexus;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class CustomerAPI
{

    private $endPoint = '';

    private $accessKey = '';
    private $accessToken = '';

    private $httpConfig = '';
    private $httpClient;
    
    private $lastQuery = null;

    public function __construct(string $_accessKey = '', string $_accessToken = '', array $_httpConfig = []) {

        // SET API Access ID - Only If Provided
        $this->accessKey = strlen($_accessKey)? $_accessKey : '';

        // SET API Access Token - Only If Provided
        $this->accessToken = strlen($_accessToken)? $_accessToken : '';   
        
        // SET Guzzle Connection Config - Only If Provided
        $this->httpConfig = array_merge([
            'verify' => false,
            'headers' => [
                'user-agent' => 'BazaarNexus API Client',
            ],
            'connect_timeout' => 20,
        ], $_httpConfig);

        // Construct Guzzle Client Interface
        try {
            $this->httpClient = new \GuzzleHttp\Client($this->httpConfig);
        } catch(\Exception $e) {
            throw new Exception('Construction failure: '. $e->getMessage());
        }

        return true;
        
    }

    public function setEndPoint(string $endPoint) {
        if(!filter_var($endPoint, FILTER_VALIDATE_URL)) return false;
        $this->endPoint = $endPoint;
        return true;
    }

    public function setAccessKey(string $accessKey = '') {
        $this->accessKey = $accessKey;
        return true;
    }

    public function setAccessToken(string $accessToken = '') {
        $this->accessToken = $accessToken;
        return true;
    }

    public function setHttpConfig(string $param, mixed $value = null) {
        $this->httpConfig[$param] = $value;
        try {
            $this->httpClient = new \GuzzleHttp\Client($this->httpConfig);
        } catch(\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return true;
    }

    public function getAccessKey() {
        return $this->accessKey;
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function getEndPoint() {
        return $this->endPoint;
    }

    public function getLastQuery() {
        return $this->lastQuery? $this->lastQuery : null;
    }

    public function getHttpConfig() {
        return $this->httpConfig;
    }

    public function sendRequest(array $requestObject) {

        if(!$this->endPoint) throw new \Exception('API EndPoint is required');

        $requestObject = $this->signRequest($requestObject);

        $this->lastQuery = http_build_query($requestObject);

        $apiRequest = $this->httpClient->request('POST', $this->endPoint, [
            'form_params' => $requestObject,
            'headers' => [
                'bazaarnexus-authmode' => 'customer',
                'bazaarnexus-authapi' => $this->accessKey,
            ],
        ]);

		if(!$apiRequest->getStatusCode()) {
            $reasonPhrase = $apiRequest->getReasonPhrase();
            if(!$reasonPhrase) $reasonPhrase = 'HTTP request failed, Unknown error';
            throw new \Exception($resultResponse);
        }

        $apiResponse = $apiRequest->getBody()->getContents();
        $apiResponse = $this->checkResponse($apiResponse);
        if(!$apiResponse || !is_array($apiResponse)) throw new \Exception('Invalid API Response');

        return $apiResponse;
    }

    public function checkSuccess($requestResponse) {
        if(!$this->checkResponse($requestResponse)) return false;
        return ($requestResponse['status'] === 'success')? true : false;
    }

    public function redirect($url) {
        if(!filter_var($url, FILTER_VALIDATE_URL)) throw new \Exception('Invalid Redirection URL');
        exit(header("Location: $url"));
    }

    public function getMessage($requestResponse) {
        if(!$this->checkResponse($requestResponse)) return 'Empty Message / No Description';
        return isset($requestResponse['message'])? $requestResponse['message'] : 'Empty Message / No Description';
    }

    public function getData($requestResponse) {
        if(!$this->checkResponse($requestResponse) || !isset($requestResponse['data'])) return [];
        return $requestResponse['data'];
    }

    private function checkResponse($apiResponse = null) {
        if(!$apiResponse) return false;

        if(!is_array($apiResponse)) {
            json_decode($apiResponse,true);
            if(json_last_error() !== JSON_ERROR_NONE) return false;
            $apiResponse = json_decode($apiResponse,true);
        }
        if(!is_array($apiResponse) || !isset($apiResponse['status']) || !array_key_exists('message', $apiResponse)) 
            return false;

        return $apiResponse;
    }

    private function signRequest(array $requestObject = []) {

        unset($requestObject['apisign'], $requestObject['apinonce']);
        ksort($requestObject);

        $requestEncoding = [];
        foreach($requestObject as $key => $value) {
            if(is_array($value)) foreach($value as $vk => $vv) {
                $requestEncoding[] = implode('-', [(string) $key, (string) $vk, (is_string($vk) || is_numeric($vk)? (string) $vk : (is_bool($v)? ($v? 1 : 0) : 'object'))]);
            } else $requestEncoding[] = implode('-', [(string) $key, (is_string($vk) || is_numeric($vk)? (string) $vk : (is_bool($v)? ($v? 1 : 0) : 'object'))]);
        }
        $requestEncoding = implode(',', $requestEncoding);

        $apiNonce = uniqid();
        $apiSign = hash_hmac('sha256', $requestEncoding, $this->accessToken . ((string) $apiNonce));

        $requestObject['apisign'] = $apiSign;
        $requestObject['apinonce'] = $apiNonce;  

        return $requestObject;
    }

  
}
