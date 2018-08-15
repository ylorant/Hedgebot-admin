<?php
namespace Hedgebot\CoreBundle\Controller;

use Hedgebot\CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Hedgebot\CoreBundle\Form\TwitchTokenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Hedgebot\CoreBundle\Service\TwitchClientService;
use Symfony\Component\HttpFoundation\JsonResponse;

class StoreController extends BaseController
{
    /**
     * AJAX call - Gets the store from the bot.
     * 
     * @param Request $request The request. The following GET parameters are accepted:
     *                         - sourceNamespace: The source namespace restraint, to limit the store fetching to only
     *                           a certain source.
     *                         - channel: The channel for which to get the store content. Makes the store content vary
     *                           depending on the channel it is shown on.
     * 
     * @return JsonResponse A JSON Reponse containing the state of the store.
     * 
     * @Route("/store", name="store_get", options = { "expose" = true })
     */
    public function getStoreAction(Request $request)
    {
        $channel = $request->query->get('channel', null);
        $sourceNamespace = $request->query->get('sourceNamespace', null);

        $storeEndpoint = $this->get('hedgebot_api')->endpoint('/store');
        $storeContent = $storeEndpoint->getStoreData($channel, $sourceNamespace);

        $response = new JsonResponse();
        $response->setData($storeContent);

        return $response;
    }

    /**
     * AJAX call - Applies a text formatter on the given text.
     * 
     * @param Request $request The request. The following GET parameters are accepted:
     *                         - sourceNamespace: The source namespace restraint, to limit the store fetching to only
     *                           a certain source.
     *                         - root: The root path to use for all the token replacements.
     *                         - channel: The channel for which to get the store content. Makes the store content vary
     *                           depending on the channel it is shown on.
     *                         - text: The text to format.
     * 
     * @return JsonResponse A JSON response containing the formatted text.
     */
    public function formatText(Request $request)
    {
        $channel = $request->query->get('channel', null);
        $sourceNamespace = $request->query->get('sourceNamespace', null);
        $root = $request->query->get('root', null);

        $storeEndpoint = $this->get('hedgebot_api')->endpoint('/store');
        $storeContent = $storeEndpoint->format($channel, $sourceNamespace);
    }
}