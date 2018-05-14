<?php
namespace Hedgebot\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    public function beforeActionHook()
    {
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Home", $this->get("router")->generate("dashboard"));
    }
}
