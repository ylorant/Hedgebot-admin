<?php
namespace Hedgebot\CoreBundle\API;

use Hedgebot\CoreBundle\Exception\RPCException;
use Curl\Curl;

/**
 * JSON-RPC HTTP API client. Simple and polyvalent remote procedure call protocol, using HTTP POST queries as a transport.
 * This client works partly as a singleton, and partly as an instancied client. The base client is a singleton, but
 * for each endpoint, a new instance will be created.
 */
class Client
{
    private $baseUrl; ///< Base webservice URL
    private $accessToken; ///< Access token
    private $endpoint; ///< Section name.
    private $id = 1; ///< Current query ID
    
    /**
     * Sets the security access token that will be used in all queries.
     * For security reasons, it is impossible to read it back once it has been entered.
     * @param string $token The access token.
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    /**
     * Sets the base URL for API calls.
     * @param string $url The base URL to set.
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = rtrim($url, '/');
    }

    /**
     * Sets the endpoint.
     * @param  string $name The endpoint name.
     * @return Client       A new client.
     */
    public function setEndpoint($name)
    {
        $this->endpoint = ltrim($name, '/');
        
        return $this;
    }
    
    /**
     * Call an API method on the current endpoint.
     * @param  string $name The name of the called API method
     * @param  array  $args Array containing the arguments. If the array has as the only argument a NamedArgs instance, named args will be used.
     * @return mixed        The API method return.
     *
     * @throws RPCError
     */
    public function __call($name, $args)
    {
        // Basic JSON-RPC call
        $data = ['jsonrpc' => '2.0', 'method' => $name, 'id' => $this->id++, 'params' => []];
        
        if(!empty($args))
        {
            if(count($args) == 1 && $args[0] instanceof NamedArgs)
                $data['params'] = $args[0]->toArray();
            else
                $data['params'] = $args;
        }
        
        $query = new Curl();
        $query->setHeader('X-Token', $this->accessToken);
        $query->setHeader('Content-Type', 'application/json');
        $query->setHeader('Accept', 'application/json');
        
        $query->post($this->baseUrl.'/'. $this->endpoint, $data);
        
        if(!empty($query->response))
        {
            // Check that the call succeeded
            if($query->httpStatusCode == 200)
            {
                $json = $query->response;
                if(!empty($json->error))
                    throw new RPCException($json->error->message, $json->error->code);
            }
            else
                throw new RPCException($query->response, $query->httpStatusCode);
            
            return $json->result;
        }
        elseif($query->error == true) // Handle errors
            throw new RPCException($query->errorMessage, $query->errorCode);
    }
}