<?php
namespace Hedgebot\CoreBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Hedgebot\CoreBundle\Form\TwitchTokenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Hedgebot\CoreBundle\Service\TwitchClientService;

class TwitchController extends BaseController
{
    const TWITCH_AUTH_URL_TEMPLATE = "https://id.twitch.tv/oauth2/authorize?client_id={client_id}&redirect_uri={redirect_uri}&response_type=token&scope={scopes}";

    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Twitch API", $this->generateUrl("twitch_index"));
    }

    /** Twitch index page.
     * Lists the access tokens.
     *
     * @Route("/twitch", name="twitch_index")
     */
    public function indexAction()
    {
        $templateVars = [];
        $twitchApi = $this->get('twitch_client');
        $twitchEndpoint = $this->get('hedgebot_api')->endpoint('/twitch');
        
        $templateVars['tokens'] = (array) $twitchEndpoint->getAccessTokens();
        $templateVars['twitch_auth_url'] = $twitchApi->getAuthenticationUrl();

        return $this->render('HedgebotCoreBundle::route/twitch/index.html.twig', $templateVars);
    }

    /** Token creation initial page / Twitch OAuth redirect page.
     * This page will allow the user to choose for which channel the token that has been created will be assigned.
     *
     * @Route("/twitch_oauth/redirect", name="twitch_oauth_redirect")
     */
    public function newTokenAction(Request $request)
    {
        $twitchEndpoint = $this->get('hedgebot_api')->endpoint('/twitch');
        
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
            $twitchApi = $this->get('twitch_client');
            $tokenInfo = $twitchApi->getAccessCredentials($accessCode);

            if (!empty($tokenInfo['error'])) {
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
        return $this->render('HedgebotCoreBundle::route/twitch/new-token.html.twig', $templateVars);
    }
    
    /** Page where a new token is created.
     * This page will allow the user to choose for which channel the token will be created.
     *
     * @Route("/twitch/token/revoke/{channel}", name="twitch_token_revoke")
     */
    public function revokeTokenAction($channel)
    {
        $twitchEndpoint = $this->get('hedgebot_api')->endpoint('/twitch');
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
