<?php
namespace Hedgebot\CoreBundle\Service;

use Hedgebot\CoreBundle\API\Client;

class ApiClientService
{
    private $uri;
    
    /**
     * Builds an API client for the given API base URI
     */
    public function __construct($uri, $token = null)
    {
        $this->client = new Client();
        $this->client->setBaseUrl($uri);
        
        if (!empty($token)) {
            $this->client->setAccessToken($token);
        }
    }
    
    /**
     * Sets the endpoint and gets a new Client instance.
     * @param  string $name The endpoint name.
     * @return Client       A new client.
     */
    public function endpoint($name)
    {
        $client = clone $this->client;
        $client->setEndpoint($name);

        return $client;
    }
}
