<?php
namespace Hedgebot\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Forwarded trust event subscriber.
 * Subscribes to the kernel.request events to allow trusting reverse proxy X-Forwarded-* 
 * headers depending on config.
 * 
 * @package Hedgebot\CoreBundle\EventSubscriber
 */
class ForwardedTrustSubscriber implements EventSubscriberInterface
{
    protected $trustedProxies = null;

    public function __construct($trustedProxies)
    {
        $this->trustedProxies = $trustedProxies;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(!empty($this->trustedProxies)) {
            Request::setTrustedProxies($this->trustedProxies, Request::HEADER_X_FORWARDED_ALL);
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