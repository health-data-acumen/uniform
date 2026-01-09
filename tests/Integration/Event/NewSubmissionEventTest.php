<?php

namespace App\Tests\Integration\Event;

use App\Dto\FormSubmissionDto;
use App\Event\NewSubmissionEvent;
use App\EventListener\SubmissionListener;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class NewSubmissionEventTest extends DatabaseTestCase
{
    public function testNewSubmissionEventIsDispatchedWithCorrectData(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');
        $submission = $this->createFormSubmission($form, ['email' => 'test@example.com', 'message' => 'Hello']);

        $dto = FormSubmissionDto::fromSubmission($submission);
        $event = new NewSubmissionEvent($dto);

        $this->assertSame($dto, $event->submission);
        $this->assertSame($submission->getId(), $dto->id);
        $this->assertSame('test@example.com', $dto->payload['email']);
    }

    public function testSubmissionListenerDispatchesMessageOnEvent(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');
        $submission = $this->createFormSubmission($form, ['email' => 'listener@example.com']);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) use ($submission) {
                return $message->getSubmissionId() === $submission->getId();
            }))
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()));

        $listener = new SubmissionListener($messageBus);
        $dto = FormSubmissionDto::fromSubmission($submission);
        $event = new NewSubmissionEvent($dto);

        $listener($event);
    }

    public function testEventDispatcherIntegration(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Integration Form');
        $submission = $this->createFormSubmission($form, ['name' => 'Test']);

        $eventDispatcher = self::getContainer()->get('event_dispatcher');

        $eventFired = false;
        $capturedEvent = null;

        $eventDispatcher->addListener(NewSubmissionEvent::class, function (NewSubmissionEvent $event) use (&$eventFired, &$capturedEvent) {
            $eventFired = true;
            $capturedEvent = $event;
        });

        $dto = FormSubmissionDto::fromSubmission($submission);
        $eventDispatcher->dispatch(new NewSubmissionEvent($dto));

        $this->assertTrue($eventFired, 'Event should have been fired');
        $this->assertNotNull($capturedEvent);
        $this->assertSame($submission->getId(), $capturedEvent->submission->id);
    }
}
