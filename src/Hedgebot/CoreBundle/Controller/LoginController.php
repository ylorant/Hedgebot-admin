<?php
namespace Hedgebot\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LoginController extends BaseController
{
	/**
     * @Route("/login", name="login")
     */
	public function indexAction(Request $request)
	{
	    $authenticationUtils = $this->get('security.authentication_utils');
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('HedgebotCoreBundle::login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
	}
}