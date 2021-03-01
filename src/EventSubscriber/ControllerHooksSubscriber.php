<?php
namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ReflectionClass;

class ControllerHooksSubscriber implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        try {
                $controllerReflector = new ReflectionClass($controller);

            if ($controllerReflector->hasMethod('beforeActionHook') && $controllerReflector->getMethod('beforeActionHook')->isPublic()) {
                $controller->beforeActionHook();
            }
        } catch (ReflectionException $e) {
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }
}
