<?php

namespace KikCMS\Domain\App;

use KikCMS\Domain\App\Exception\ObjectNotFoundHttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ObjectNotFoundHttpException) {
            $response = new JsonResponse(['error' => $exception->getMessage() ?: 'Object not found']);
            $event->setResponse($response);
        }
    }
}