<?php

namespace App\Controller;

use App\Service\ApiClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TwitchApi\Exceptions\ClientIdRequiredException;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BaseController extends AbstractController
{
    /** @var Breadcrumbs */
    public $breadcrumbs;
    /** @var TranslatorInterface */
    public $translator;
    /** @var ApiClientService */
    public $apiClientService;

    /**
     * Constructor
     * @param RouterInterface $routerInterface
     * @param TranslatorInterface $translator
     * @param Breadcrumbs $breadcrumbs
     * @param $apiBaseUrl
     * @param $apiAccessToken
     * @throws ClientIdRequiredException
     */
    public function __construct(
        TranslatorInterface $translator,
        Breadcrumbs $breadcrumbs,
        $apiBaseUrl,
        $apiAccessToken
    ) {
        $this->breadcrumbs = $breadcrumbs;
        $this->apiClientService = new ApiClientService($apiBaseUrl, $apiAccessToken);
        $this->translator = $translator;
    }

    /**
     * Base hook executed before the call of the action on the controller. Allows to
     * define a controller-wide breadcrumbs here.
     *
     * @return void
     */
    public function beforeActionHook()
    {
        $this->breadcrumbs->addItem(
            'title.dashboard',
            $this->get('router')->generate("dashboard")
        );
    }
}
