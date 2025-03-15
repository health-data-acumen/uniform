<?php

namespace App\EventListener\Turbo;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class TurboRedirectResponseListener
{
    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$this->shouldRedirect($request, $response)) {
            return;
        }

        $newResponse = new Response(null, Response::HTTP_NO_CONTENT);
        $newResponse->headers->set('X-Turbo-Location', $response->headers->get('Location'));

        $event->setResponse($newResponse);
    }

    protected function shouldRedirect(Request $request, Response $response): bool
    {
        return $response->isRedirect() && $request->headers->get('turbo-frame-redirect');
    }
}
