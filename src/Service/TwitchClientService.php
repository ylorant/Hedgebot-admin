<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use TwitchApi\Exceptions\ClientIdRequiredException;
use TwitchApi\Exceptions\InvalidTypeException;
use TwitchClient\API\Auth\Authentication;
use TwitchClient\API\Helix\Helix;
use TwitchClient\Authentication\DefaultTokenProvider;
use TwitchClient\Authentication\TokenProvider;

class TwitchClientService
{
    /** @var RouterInterface The Twitch router, to be able to pull the auth redirect Uri */
    protected RouterInterface $router;
    /** @var string The route name that should be given as redirect Uri */
    protected string $redirectRoute;
    protected TokenProvider $tokenProvider;
    /** @var array The access scope for new token requests */
    protected array $scope;
    /** @var LoggerInterface The logger to log errors */
    protected LoggerInterface $logger;
    /** @var Helix Twitch API client */
    protected Helix $client;

    /**
     * Constructor.
     *
     * @param RouterInterface $router The Symfony router, to resolve routes.
     * @param string $redirectRoute The route name that should be used to generate the Redirect URI from.
     * @throws ClientIdRequiredException
     */
    public function __construct(RouterInterface $router, LoggerInterface $logger, string $redirectRoute = 'twitch_oauth_redirect')
    {
        $this->router = $router;
        $this->redirectRoute = $redirectRoute;
        $this->tokenProvider = new DefaultTokenProvider(null, null);
        $this->logger = $logger;
    }

    /**
     * Pulls necessary info from the given Hedgebot API client to allow the Twitch client to work.
     *
     * @param ApiClientService $hedgebotClient The Hedgebot API client.
     */
    public function initFromHedgebotApiClient(ApiClientService $hedgebotClient)
    {
        $twitchEndpoint = $hedgebotClient->endpoint('/twitch');
        $clientID = $twitchEndpoint->getClientID();
        $clientSecret = $twitchEndpoint->getClientsecret();

        $this->tokenProvider = new DefaultTokenProvider($clientID, $clientSecret);
        $this->client = new Helix($this->tokenProvider);
    }

    /**
     * Fills the URL template that is stored as a constant with the given data to generate a valid
     * Twitch OAuth URL.
     *
     * @return string
     */
    public function getAuthenticationUrl(): string
    {
        $authApi = new Authentication($this->tokenProvider);
        $redirectUri = $this->router->generate($this->redirectRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return $authApi->getAuthorizeURL($redirectUri, $this->scope);
    }

    public function getAccessToken(string $code, bool $register = false)
    {
        $redirectUri = $this->router->generate($this->redirectRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $authApi = new Authentication($this->tokenProvider);
        $token = $authApi->getAccessToken($code, $redirectUri);

        if (!$token) {
            $error = $authApi->getLastError();
            $this->logger->error("Twitch getAccessToken error(" . $error['errno'] . "): " . $error['error']);
        }

        if ($token && $register) {
            $this->registerAccessToken($token['token'], $token['refresh']);
        }

        return $token;
    }

    public function registerAccessToken(string $token, string $refresh, string $username = null)
    {
        if (empty($username)) {
            $this->tokenProvider->setDefaultAccessToken($token);
            $this->tokenProvider->setDefaultRefreshToken($refresh);
        } else {
            $this->tokenProvider->setAccessToken($username, $token);
            $this->tokenProvider->setRefreshToken($username, $refresh);
        }
    }

    public function getUsername()
    {
        $user = $this->getUserInfo();
        return $user->login;
    }

    public function getUserInfo()
    {
        return $this->client->users->getUser();
    }

    /**
     * Set scope
     *
     * @param array $scope
     * @throws InvalidTypeException
     */
    public function setScope(array $scope)
    {
        $this->scope = $scope;
    }
}
