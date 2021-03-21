<?php

namespace App\Controller;

use App\Service\ApiClientService;
use App\Service\TwitchClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TwitchApi\Exceptions\ClientIdRequiredException;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BaseController extends AbstractController
{
    /**
     * @var Breadcrumbs
     */
    public $breadcrumbs;
    /**
     * @var TranslatorInterface
     */
    public $translator;
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
     * @param TranslatorInterface $translator
     * @param $apiBaseUrl
     * @param $apiAccessToken
     * @throws ClientIdRequiredException
     */
    public function __construct(RouterInterface $routerInterface, TranslatorInterface $translator, $apiBaseUrl, $apiAccessToken)
    {
        $this->breadcrumbs = new Breadcrumbs();
        $this->apiClientService = new ApiClientService($apiBaseUrl, $apiAccessToken);
        $this->twitchClientService = new TwitchClientService($routerInterface);
        $this->translator = $translator;
    }

    public function beforeActionHook()
    {
        $this->breadcrumbs->addItem(
            $this->translator->trans('title.dashboard'),
            $this->get('router')->generate("dashboard")
        );
    }
}