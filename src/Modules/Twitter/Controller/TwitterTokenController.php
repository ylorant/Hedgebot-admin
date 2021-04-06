<?php

namespace App\Modules\Twitter\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
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

        // Bad "breandcrumb x translator" usage, @see https://github.com/mhujer/BreadcrumbsBundle/issues/26
        $this->breadcrumbs->addItem($this->translator->trans('title.twitter', [], 'twitter'));
        $this->breadcrumbs->addItem($this->translator->trans('title.tokens', [], 'twitter'), $this->generateUrl("twitter_token_list"));
    }

    /**
     * @Route("/twitter/tokens", name="twitter_token_list")
     */
    public function list()
    {
        $templateVars = [];
        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');

        $templateVars['tokens'] = (array) $endpoint->getAccessTokens();

        return $this->render('twitter/route/token-list.html.twig', $templateVars);
    }

    /**
     * @Route("/twitter/oauth_init", name="twitter_init_oauth")
     */
    public function initOAuth()
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');
        $authorizeUrl = $endpoint->getAuthorizeUrl();

        return $this->redirect($authorizeUrl);
    }

    /**
     * @Route("/twitter/oauth_redirect", name="twitter_new_token")
     * @param Request $request
     * @return RedirectResponse
     */
    public function newToken(Request $request)
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
     * @param $account
     * @return RedirectResponse
     */
    public function deleteToken($account)
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
