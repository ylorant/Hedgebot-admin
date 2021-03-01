<?php
namespace App\Controller;

use App\Service\ApiClientService;
use App\Service\TwitchClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use TwitchApi\Exceptions\ClientIdRequiredException;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BaseController extends AbstractController
{
    /**
     * @var Breadcrumbs
     */
    public $breadcrumbs;
    /**
     * @var ApiClientService
     */
    public $apiClientService;
    /**
     * @var TwitchClientService
     */
    public $twitchClientService;

    /**
     * Constructor
     * @param RouterInterface $routerInterface
     * @param $apiBaseUrl
     * @param $apiAccessToken
     * @throws ClientIdRequiredException
     */
    public function __construct(RouterInterface $routerInterface, $apiBaseUrl, $apiAccessToken)
    {
        $this->breadcrumbs = new Breadcrumbs();
        $this->apiClientService = new ApiClientService($apiBaseUrl, $apiAccessToken);
        $this->twitchClientService = new TwitchClientService($routerInterface);
    }

    public function beforeActionHook()
    {
        $this->breadcrumbs->addItem("home", $this->get('router')->generate("dashboard"));
    }
}
