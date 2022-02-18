<?php

namespace App\Controller;

use App\Service\ApiClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BaseController extends AbstractController
{
    protected RouterInterface $router;

    public Breadcrumbs $breadcrumbs;

    public TranslatorInterface $translator;

    public ApiClientService $apiClientService;

    /**
     * Constructor
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param Breadcrumbs $breadcrumbs
     * @param ApiClientService $apiClientService
     */
    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        Breadcrumbs $breadcrumbs,
        ApiClientService $apiClientService
    ) {
        $this->breadcrumbs = $breadcrumbs;
        $this->apiClientService = $apiClientService;
        $this->translator = $translator;
        $this->router = $router;
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
            $this->generateUrl("dashboard")
        );
    }
}
