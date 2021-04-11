<?php
namespace App\Modules\Twitter\Controller;

use App\Controller\BaseController;
use App\Modules\Twitter\Enum\StatusEnum;
use App\Modules\Twitter\Form\TweetFilterType;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Modules\Twitter\Form\TweetType;
use Symfony\Component\HttpFoundation\File\File;
use DateTime;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;

class TwitterTweetController extends BaseController
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
    }

    /**
     * @Route("/twitter/tweets", name="twitter_tweet_list")
     */
    public function listAction(Request $request)
    {
        $this->breadcrumbs->addItem($this->translator->trans('title.tweets', [], 'twitter'));

        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');
        $serverEndpoint = $this->apiClientService->endpoint('/server');
        $accounts = (array) $endpoint->getAccessTokenAccounts();
        $channels = $serverEndpoint->getAvailableChannels();
        $filters = [
            'status' => [StatusEnum::DRAFT, StatusEnum::SCHEDULED]
        ];

        $filterForm = $this->createForm(TweetFilterType::class, $filters, [
            'method' => 'GET', 
            'accounts' => $accounts, 
            'channels' => $channels
        ]);
        $filterForm->handleRequest($request);

        if($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filters = array_filter($filterForm->getData());
        }

        $templateVars = [];
        $templateVars['filterForm'] = $filterForm->createView();
        $templateVars['tweets'] = (array) $endpoint->getScheduledTweets($filters);
        $templateVars['statusLabels'] = StatusEnum::getLabels();
        $templateVars['statusBadges'] = StatusEnum::getBadgeClasses();

        return $this->render('twitter/route/tweet-list.html.twig', $templateVars);
    }

    /**
     * @Route("/twitter/tweets/new", name="twitter_tweet_new")
     * @Route("/twitter/tweets/edit/{tweetId}", name="twitter_tweet_edit")
     * @param Request $request
     * @param null $tweetId
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function tweetAction(Request $request, $tweetId = null)
    {
        $templateVars = [];
        $tweet = null;
        $formTweet = null;
        $tweetStatusClass = null;

        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');

        // Get the scheduled tweet if it's an edition
        if (!empty($tweetId)) {
            $tweet = $endpoint->getScheduledTweet($tweetId);

            // Cloning the tweet for the form, because it will be modified by the request handling
            $formTweet = clone $tweet;

            // Adapting the tweet from the API to its form structure
            $formTweet->media = new File(reset($tweet->media), false);

            if (!empty($formTweet->sentTime)) {
                $formTweet->sentTime = new DateTime($formTweet->sentTime);
            }

            if (!empty($formTweet->sendTime)) {
                $formTweet->sendTime = new DateTime($formTweet->sendTime);
                $formTweet->trigger = 'datetime';
            } else {
                $formTweet->trigger = 'event';
            }

            if (is_array($formTweet->constraints)) {
                foreach ($formTweet->constraints as &$constraint) {
                    $constraint = (array) $constraint;
                }
            }

            $tweetStatusClass = StatusEnum::getBadgeClass($tweet->status);
        }

        $translationSource = !empty($tweetId) ? 'title.edit_tweet' : 'title.new_tweet';
        $this->breadcrumbs->addItem($this->translator->trans($translationSource, [], 'twitter'));

        $serverEndpoint = $this->apiClientService->endpoint('/server');
        $accounts = (array) $endpoint->getAccessTokenAccounts();
        $channels = $serverEndpoint->getAvailableChannels();

        $form = $this->createForm(TweetType::class, $formTweet, ['accounts' => $accounts, 'channels' => $channels]);
        $form->handleRequest($request);

        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // If no previous tweet is present, fetch the form tweet from the form itself
            if (empty($formTweet)) {
                $formTweet = (object) $form->getData();
            }

            try {
                if (!empty($formTweet->media)) {
                    $file = $formTweet->media;

                    // Move the file to the directory where brochures are stored
                    $newPath = [
                        "dir" => rtrim($this->getParameter('app.upload_directory'), "/"). "/twitter/",
                        "name"=> $this->generateUniqueFileName($file)
                    ];

                    $file->move($newPath["dir"], $newPath["name"]);

                    // Generating the HTTP URL from the path
                    $mediaHttpUrl = $newPath["dir"]. $newPath["name"];
                    $mediaHttpUrl = str_replace($this->get('kernel')->getProjectDir(). "/public", $request->getSchemeAndHttpHost(), $mediaHttpUrl);
                    $formTweet->media = [$mediaHttpUrl];
                } elseif (!empty($tweet->media)) {
                    // If there is no new media uploaded, keep the old one
                    $formTweet->media = $tweet->media;
                } else {
                    $formTweet->media = [];
                }

                // Format times
                if (!empty($formTweet->sendTime) && $formTweet->sendTime instanceof DateTime) {
                    $formTweet->sendTime = $formTweet->sendTime->format('Y-m-d H:i');
                }

                // Format constraints
                $formTweet->constraints = array_values($formTweet->constraints);

                $tweetId = $endpoint->saveScheduledTweet($formTweet);

                if ($tweetId) {
                    $this->addFlash('success', 'The tweet has been saved successfully.');
                    return $this->redirectToRoute('twitter_tweet_edit', ['tweetId' => $tweetId]);
                } else {
                    $this->addFlash('danger', 'An error occured while saving tweet.');
                }
            } catch (FileException $e) {
                $this->addFlash('danger', 'Cannot upload the media file.');
            }
        }

        $templateVars['form'] = $form->createView();
        $templateVars['tweet'] = $tweet;
        $templateVars['statusClass'] = $tweetStatusClass;

        return $this->render('twitter/route/tweet.html.twig', $templateVars);
    }

    /**
     * @Route("/twitter/tweets/delete/{tweetId}", name="twitter_tweet_delete")
     * @param $tweetId
     * @return RedirectResponse
     */
    public function deleteTweetAction($tweetId)
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');

        $deleted = $endpoint->deleteScheduledTweet($tweetId);
        if ($deleted) {
            $this->addFlash("success", "Tweet deleted.");
        } else {
            $this->addFlash("danger", "Failed to delete tweet.");
        }

        return $this->redirectToRoute('twitter_tweet_list');
    }

    /**
     * @Route("/twitter/tweets/send/{tweetId}", name="twitter_tweet_send")
     * @param $tweetId
     * @return RedirectResponse
     */
    public function sendTweetAction($tweetId)
    {
        $endpoint = $this->apiClientService->endpoint('/plugin/twitter');
        $sent = $endpoint->sendScheduledTweet($tweetId);

        if ($sent) {
            $tweet = $endpoint->getScheduledTweet($tweetId);

            $this->addFlash("success", "Tweet sent.");

            // Redirect to either the tweet page or to the list page depending on the tweet status
            if (!empty($tweet)) {
                return $this->redirectToRoute('twitter_tweet_edit', ["tweetId" => $tweetId]);
            } else {
                return $this->redirectToRoute('twitter_tweet_list');
            }
        } else {
            $this->addFlash("danger", "Failed to send tweet.");
            return $this->redirectToRoute('twitter_tweet_edit', ["tweetId" => $tweetId]);
        }
    }

    /**
     * Generates an unique filename for an uploaded file.
     *
     * @param File $file The file to generate the unique name for.
     *
     * @return string The generated filename.
     *
     * TODO: Generate extensions
     */
    private function generateUniqueFileName(File $file)
    {
        return md5(uniqid());
    }
}
