<?php

namespace App\Tests\Unit\EventListener;

use App\Dto\FormSubmissionDto;
use App\Event\NewSubmissionEvent;
use App\EventListener\SubmissionListener;
use App\Message\Command\SendSubmissionNotification;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SubmissionListenerTest extends TestCase
{
    public function testInvokeDispatchesSendSubmissionNotificationMessage(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SendSubmissionNotification::class))
            ->willReturn(new Envelope(new \stdClass()));

        $listener = new SubmissionListener($messageBus);

        $submissionDto = new FormSubmissionDto(
            id: 123,
            payload: [],
            formId: 456
        );
        $event = new NewSubmissionEvent($submissionDto);

        $listener($event);
    }

    public function testInvokePassesSubmissionIdToMessage(): void
    {
        $capturedMessage = null;

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) use (&$capturedMessage) {
                $capturedMessage = $message;

                return $message instanceof SendSubmissionNotification;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $listener = new SubmissionListener($messageBus);

        $submissionDto = new FormSubmissionDto(
            id: 999,
            payload: ['email' => 'test@example.com'],
            formId: 1
        );
        $event = new NewSubmissionEvent($submissionDto);

        $listener($event);

        $this->assertInstanceOf(SendSubmissionNotification::class, $capturedMessage);
        $this->assertSame(999, $capturedMessage->getSubmissionId());
    }

    public function testInvokeUsesSubmissionIdFromEvent(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (SendSubmissionNotification $message) {
                return $message->getSubmissionId() === 42;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $listener = new SubmissionListener($messageBus);

        $submissionDto = new FormSubmissionDto(
            id: 42,
            payload: [],
            formId: 1
        );
        $event = new NewSubmissionEvent($submissionDto);

        $listener($event);
    }
}
