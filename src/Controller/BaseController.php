<?php

namespace App\Controller;

use App\Service\ApiClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BaseController extends AbstractController
{
    public Breadcrumbs $breadcrumbs;

    public TranslatorInterface $translator;

    public ApiClientService $apiClientService;

    /**
     * Constructor
     * @param TranslatorInterface $translator
     * @param Breadcrumbs $breadcrumbs
     * @param ApiClientService $apiClientService
     */
    public function __construct(
        TranslatorInterface $translator,
        Breadcrumbs $breadcrumbs,
        ApiClientService $apiClientService
    ) {
        $this->breadcrumbs = $breadcrumbs;
        $this->apiClientService = $apiClientService;
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
            $this->generateUrl("dashboard")
        );
    }
}
