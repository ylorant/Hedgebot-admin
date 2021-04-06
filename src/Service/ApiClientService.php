<?php
namespace App\Service;

use HedgebotApi\Client as HedgebotApiClient;

class ApiClientService extends HedgebotApiClient
{
    public function __construct($apiBaseUrl, $apiAccessToken)
    {
        parent::__construct($apiBaseUrl, $apiAccessToken);
    }
}
