<?php
namespace App\Controller;

use App\Service\TwitchClientService;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\TwitchTokenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TwitchController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $this->breadcrumbs->addItem(
            $this->translator->trans('title.twitch_api'),
            $this->generateUrl("twitch_index")
        );
    }

    /** Twitch index page.
     * Lists the access tokens.
     *
     * @Route("/twitch", name="twitch_index")
     * @param TwitchClientService $twitchApi
     */
    public function indexAction(TwitchClientService $twitchApi)
    {
        $templateVars = [];
        $twitchEndpoint = $this->apiClientService->endpoint('/twitch');

        $templateVars['tokens'] = (array) $twitchEndpoint->getAccessTokens();
        $templateVars['twitch_auth_url'] = $twitchApi->getAuthenticationUrl();

        return $this->render('core/route/twitch/index.html.twig', $templateVars);
    }

    /** Token creation initial page / Twitch OAuth redirect page.
     * This page will allow the user to choose for which channel the token that has been created will be assigned.
     *
     * @Route("/twitch_oauth/redirect", name="twitch_oauth_redirect")
     * @param Request $request
     * @param TwitchClientService $twitchApi
     * @return RedirectResponse|Response
     */
    public function newTokenAction(Request $request, TwitchClientService $twitchApi)
    {
        $twitchEndpoint = $this->apiClientService->endpoint('/twitch');

        // Get the new token form
        $newTokenForm = $this->createForm(TwitchTokenType::class);
        $newTokenForm->handleRequest($request);

        if ($newTokenForm->isSubmitted()) {
            if ($newTokenForm->isValid()) {
                $formData = $newTokenForm->getData();
                $result = $twitchEndpoint->addAccessToken($formData['channel'], $formData['access_token'], $formData['refresh_token']);

                if ($result) {
                    $this->addFlash('success', 'Successfully saved token.');
                    return $this->redirectToRoute('twitch_index');
                } else { // Failure to create the token is surely because there is already one for the channel in the database
                    $this->addFlash('danger', 'Cannot save token: another token already exists for this channel.'.
                                              'If you want to change the token for the channel, delete it then create another one.');
                }
            }
        } else {
            // Get the token from the code given by Twitch
            $accessCode = $request->query->get('code');
            $tokenInfo = $twitchApi->getAccessCredentials($accessCode);
            
            if (isset($tokenInfo['status']) && $tokenInfo['status'] !== 200) {
                $this->addFlash('danger', 'Failed to fetch token.');
                return $this->redirectToRoute('twitch_index');
            }

            // Get the channel name to use for the channel field's default value
            $channelInfo = $twitchApi->getAuthenticatedChannel($tokenInfo['access_token']);

            $newTokenForm->setData([
                'access_token' => $tokenInfo['access_token'],
                'refresh_token' => $tokenInfo['refresh_token'],
                'channel' => $channelInfo['name']
            ]);
        }

        $templateVars['newTokenForm'] = $newTokenForm->createView();
        return $this->render('core/route/twitch/new-token.html.twig', $templateVars);
    }

    /** Page where a new token is created.
     * This page will allow the user to choose for which channel the token will be created.
     *
     * @Route("/twitch/token/revoke/{channel}", name="twitch_token_revoke")
     * @param $channel
     * @return RedirectResponse
     */
    public function revokeTokenAction($channel)
    {
        $twitchEndpoint = $this->apiClientService->endpoint('/twitch');
        $token = $twitchEndpoint->getAccessToken($channel);

        if (!empty($token)) {
            $twitchEndpoint->removeAccessToken($channel);
            $this->addFlash("success", "Successfully revoked token.");
        } else {
            $this->addFlash("danger", "Cannot revoke token as it doesn't exist.");
        }

        return $this->redirectToRoute('twitch_index');
    }
}
