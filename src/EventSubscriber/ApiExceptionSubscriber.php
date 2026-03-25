<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force les réponses JSON pour les erreurs sur les routes /api/*.
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // On ne touche qu'aux routes qui commencent par /api
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        // Si c'est une HttpException (404, 405, 403, 401...), on récupère le code directement
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } else {
            // Pour toute autre erreur, on renvoie un 500 générique
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $data = [
            'error' => $exception->getMessage() ?: 'Une erreur est survenue.',
        ];

        $response = new JsonResponse($data, $statusCode);

        $event->setResponse($response);
    }
}