<?php
namespace App\Modules\HedgebotTwitterBundle\Controller;

use App\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class TwitterTokenController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $this->breadcrumbs->addItem("Twitter");
        $this->breadcrumbs->addItem("Tokens", $this->generateUrl("twitter_token_list"));
    }

    /**
     * @Route("/twitter/tokens", name="twitter_token_list")
     */
    public function listAction()
    {
        $templateVars = [];
        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');

        $templateVars['tokens'] = (array) $endpoint->getAccessTokens();

        return $this->render('twitter/route/token-list.html.twig', $templateVars);
    }

    /**
     * @Route("/twitter/oauth_init", name="twitter_init_oauth")
     */
    public function initOAuthAction()
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');
        $authorizeUrl = $endpoint->getAuthorizeUrl();

        return $this->redirect($authorizeUrl);
    }

    /**
     * @Route("/twitter/oauth_redirect", name="twitter_new_token")
     */
    public function newTokenAction(Request $request)
    {
        $oauthVerifier = $request->query->get('oauth_verifier');

        if (empty($oauthVerifier)) {
            $this->addFlash('danger', 'Missing OAuth verifier for token creation.');
            return $this->redirectToRoute('twitter_token_list');
        }

        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');
        $tokenCreated = $endpoint->createAccessToken($oauthVerifier);

        if ($tokenCreated) {
            $this->addFlash('success', 'Access token created successfully.');
        } else {
            $this->addFlash('danger', 'An error occured while trying to create the access token.');
        }

        return $this->redirectToRoute('twitter_token_list');
    }

    /**
     * @Route("/twitter/tokens/delete/{account}", name="twitter_token_delete")
     */
    public function deleteTokenAction($account)
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');
        $deleted = $endpoint->deleteAccessToken($account);

        if ($deleted) {
            $this->addFlash('success', 'The token has been deleted successfully.');
        } else {
            $this->addFlash('danger', 'An error occured while deleting the token.');
        }

        return $this->redirectToRoute('twitter_token_list');
    }
}
