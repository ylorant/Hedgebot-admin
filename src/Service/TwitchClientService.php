<?php

namespace App\Service;

use Symfony\Component\Routing\RouterInterface;
use TwitchApi\Exceptions\ClientIdRequiredException;
use TwitchApi\Exceptions\InvalidTypeException;
use TwitchApi\TwitchApi;
use Symfony\Component\Routing\Generator\UrlGenerator;

class TwitchClientService
{
    /** @var TwitchApi The API client */
    protected TwitchApi $twitchApiClient;
    /** @var RouterInterface The Twitch router, to be able to pull the auth redirect Uri */
    protected RouterInterface $router;
    /** @var string The route name that should be given as redirect Uri */
    protected string $redirectRoute;

    private const TWITCH_SCOPE = ['channel_editor'];

    /**
     * Constructor.
     *
     * @param RouterInterface $router The Symfony router, to resolve routes.
     * @param string $redirectRoute The route name that should be used to generate the Redirect URI from.
     * @throws ClientIdRequiredException
     */
    public function __construct(RouterInterface $router, string $redirectRoute = 'twitch_oauth_redirect')
    {
        $this->router = $router;
        $this->redirectRoute = $redirectRoute;
        $this->twitchApiClient = new TwitchApi(['client_id' => '', 'scope' => self::TWITCH_SCOPE]);

        $redirectUri = $this->router->generate($this->redirectRoute, [], UrlGenerator::ABSOLUTE_URL);
        $this->twitchApiClient->setRedirectUri($redirectUri);
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

        $this->setClientID($clientID);
        $this->setClientSecret($clientSecret);
    }

    /**
     * Fills the URL template that is stored as a constant with the given data to generate a valid
     * Twitch OAuth URL.
     *
     * @return string
     */
    public function getAuthenticationUrl(): string
    {
        return $this->twitchApiClient->getAuthenticationUrl();
    }

    /**
     * Gets the underlying Twitch API client.
     *
     * @return TwitchApi The Twitch API client.
     */
    public function getAPIClient(): TwitchApi
    {
        return $this->twitchApiClient;
    }

    /**
     * Proxifies calls to the underlying Twitch API.
     *
     * @param string $name The called function name.
     * @param array $arguments The called function arguments.
     *
     * @return mixed The proxified call result.
     */
    public function __call(string $name, array $arguments)
    {
        return $this->twitchApiClient->$name(...$arguments);
    }

    /**
     * Set scope
     *
     * @param array $scope
     * @throws InvalidTypeException
     */
    public function setScope(array $scope)
    {
        $this->twitchApiClient->setScope($scope);
    }
}
