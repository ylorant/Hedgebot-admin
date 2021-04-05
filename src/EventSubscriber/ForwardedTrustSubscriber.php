<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Forwarded trust event subscriber.
 * Subscribes to the kernel.request events to allow trusting reverse proxy X-Forwarded-*
 * headers depending on config.
 *
 * @package Hedgebot\Core\EventSubscriber
 */
class ForwardedTrustSubscriber implements EventSubscriberInterface
{
    protected $trustedProxies = null;

    public function __construct()
    {
        $this->trustedProxies = Request::getTrustedProxies();
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!empty($this->trustedProxies)) {
            Request::setTrustedProxies($this->trustedProxies, Request::HEADER_X_FORWARDED_FOR);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 512]
        ];
    }
}
