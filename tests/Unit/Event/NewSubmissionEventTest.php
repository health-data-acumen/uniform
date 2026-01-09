<?php

namespace App\Tests\Unit\Event;

use App\Dto\FormSubmissionDto;
use App\Event\NewSubmissionEvent;
use PHPUnit\Framework\TestCase;

class NewSubmissionEventTest extends TestCase
{
    public function testConstructorSetsSubmissionProperty(): void
    {
        $submissionDto = new FormSubmissionDto(
            id: 123,
            payload: ['email' => 'test@example.com'],
            formId: 456
        );

        $event = new NewSubmissionEvent($submissionDto);

        $this->assertSame($submissionDto, $event->submission);
    }

    public function testSubmissionPropertyIsAccessible(): void
    {
        $submissionDto = new FormSubmissionDto(
            id: 1,
            payload: [],
            formId: 1
        );

        $event = new NewSubmissionEvent($submissionDto);

        $this->assertInstanceOf(FormSubmissionDto::class, $event->submission);
        $this->assertSame(1, $event->submission->id);
    }

    public function testEventPreservesSubmissionData(): void
    {
        $payload = ['name' => 'John', 'message' => 'Hello World'];
        $submissionDto = new FormSubmissionDto(
            id: 42,
            payload: $payload,
            formId: 99
        );

        $event = new NewSubmissionEvent($submissionDto);

        $this->assertSame(42, $event->submission->id);
        $this->assertSame($payload, $event->submission->payload);
        $this->assertSame(99, $event->submission->formId);
    }
}
