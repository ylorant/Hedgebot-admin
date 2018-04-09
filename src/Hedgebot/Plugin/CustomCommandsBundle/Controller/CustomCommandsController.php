<?php
namespace Hedgebot\Plugin\CustomCommandsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CustomCommandsController extends Controller
{
	/**
     * @Route("/custom-commands", name="custom_commands_list")
     */
    public function customCommandsMainAction(Request $request)
    {
        

        return new Response("Hello world!");
    }
}