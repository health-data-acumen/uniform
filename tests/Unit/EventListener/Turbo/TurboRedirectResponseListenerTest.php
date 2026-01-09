<?php

namespace App\Tests\Unit\EventListener\Turbo;

use App\EventListener\Turbo\TurboRedirectResponseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TurboRedirectResponseListenerTest extends TestCase
{
    private TurboRedirectResponseListener $listener;

    protected function setUp(): void
    {
        $this->listener = new TurboRedirectResponseListener();
    }

    public function testOnKernelResponseReturnsEarlyForSubRequest(): void
    {
        $request = new Request();
        $request->headers->set('turbo-frame-redirect', '1');

        $response = new RedirectResponse('https://example.com/redirect');

        $event = $this->createResponseEvent($request, $response, HttpKernelInterface::SUB_REQUEST);

        $this->listener->onKernelResponse($event);

        $this->assertSame($response, $event->getResponse());
        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    public function testOnKernelResponseDoesNothingForNonRedirect(): void
    {
        $request = new Request();
        $request->headers->set('turbo-frame-redirect', '1');

        $response = new Response('OK', 200);

        $event = $this->createResponseEvent($request, $response);

        $this->listener->onKernelResponse($event);

        $this->assertSame($response, $event->getResponse());
        $this->assertSame(200, $event->getResponse()->getStatusCode());
    }

    public function testOnKernelResponseDoesNothingWithoutTurboHeader(): void
    {
        $request = new Request();

        $response = new RedirectResponse('https://example.com/redirect');

        $event = $this->createResponseEvent($request, $response);

        $this->listener->onKernelResponse($event);

        $this->assertSame($response, $event->getResponse());
        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    public function testOnKernelResponseReplacesWithNoContentResponse(): void
    {
        $request = new Request();
        $request->headers->set('turbo-frame-redirect', '1');

        $response = new RedirectResponse('https://example.com/redirect');

        $event = $this->createResponseEvent($request, $response);

        $this->listener->onKernelResponse($event);

        $this->assertSame(Response::HTTP_NO_CONTENT, $event->getResponse()->getStatusCode());
    }

    public function testOnKernelResponseSetsXTurboLocationHeader(): void
    {
        $request = new Request();
        $request->headers->set('turbo-frame-redirect', '1');

        $redirectUrl = 'https://example.com/redirect-target';
        $response = new RedirectResponse($redirectUrl);

        $event = $this->createResponseEvent($request, $response);

        $this->listener->onKernelResponse($event);

        $this->assertTrue($event->getResponse()->headers->has('X-Turbo-Location'));
        $this->assertSame($redirectUrl, $event->getResponse()->headers->get('X-Turbo-Location'));
    }

    public function testOnKernelResponsePreservesLocationInHeader(): void
    {
        $request = new Request();
        $request->headers->set('turbo-frame-redirect', 'true');

        $originalLocation = '/dashboard/forms/123';
        $response = new RedirectResponse($originalLocation);

        $event = $this->createResponseEvent($request, $response);

        $this->listener->onKernelResponse($event);

        $this->assertSame($originalLocation, $event->getResponse()->headers->get('X-Turbo-Location'));
    }

    public function testOnKernelResponseNewResponseHasNoContent(): void
    {
        $request = new Request();
        $request->headers->set('turbo-frame-redirect', '1');

        $response = new RedirectResponse('https://example.com/redirect');

        $event = $this->createResponseEvent($request, $response);

        $this->listener->onKernelResponse($event);

        $this->assertEmpty($event->getResponse()->getContent());
    }

    public function testOnKernelResponseWorksWithDifferentRedirectStatusCodes(): void
    {
        $request = new Request();
        $request->headers->set('turbo-frame-redirect', '1');

        $response301 = new RedirectResponse('https://example.com', 301);
        $event301 = $this->createResponseEvent($request, $response301);
        $this->listener->onKernelResponse($event301);
        $this->assertSame(204, $event301->getResponse()->getStatusCode());

        $response302 = new RedirectResponse('https://example.com', 302);
        $event302 = $this->createResponseEvent($request, $response302);
        $this->listener->onKernelResponse($event302);
        $this->assertSame(204, $event302->getResponse()->getStatusCode());

        $response303 = new RedirectResponse('https://example.com', 303);
        $event303 = $this->createResponseEvent($request, $response303);
        $this->listener->onKernelResponse($event303);
        $this->assertSame(204, $event303->getResponse()->getStatusCode());
    }

    private function createResponseEvent(
        Request $request,
        Response $response,
        int $requestType = HttpKernelInterface::MAIN_REQUEST
    ): ResponseEvent {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ResponseEvent($kernel, $request, $requestType, $response);
    }
}
