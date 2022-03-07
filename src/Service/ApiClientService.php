<?php

namespace App\Service;

use HedgebotApi\Client as HedgebotApiClient;

class ApiClientService extends HedgebotApiClient
{
    private $apiBaseUrl;

    private $apiAccessToken;

    public function __construct(string $apiBaseUrl, ?string $apiAccessToken)
    {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiAccessToken = $apiAccessToken;

        parent::__construct($this->apiBaseUrl, $this->apiAccessToken);
    }
}
