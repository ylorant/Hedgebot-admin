<?php
namespace Hedgebot\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ReflectionClass;

class ControllerHooksSubscriber implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $controllerReflector = new ReflectionClass($controller[0]);

        if ($controllerReflector->hasMethod('beforeActionHook') && $controllerReflector->getMethod('beforeActionHook')->isPublic()) {
            $controller[0]->beforeActionHook();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }
}
