<?php

namespace App\Tests\Unit\Entity;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use PHPUnit\Framework\TestCase;

class FormSubmissionTest extends TestCase
{
    public function testGetIdReturnsNullInitially(): void
    {
        $submission = new FormSubmission();

        $this->assertNull($submission->getId());
    }

    public function testSetAndGetPayload(): void
    {
        $submission = new FormSubmission();
        $payload = ['email' => 'test@example.com', 'message' => 'Hello'];

        $result = $submission->setPayload($payload);

        $this->assertSame($payload, $submission->getPayload());
        $this->assertSame($submission, $result);
    }

    public function testGetPayloadReturnsEmptyArrayInitially(): void
    {
        $submission = new FormSubmission();

        $this->assertSame([], $submission->getPayload());
    }

    public function testPayloadCanContainNestedArrays(): void
    {
        $submission = new FormSubmission();
        $payload = [
            'user' => ['name' => 'John', 'email' => 'john@example.com'],
            'items' => ['item1', 'item2'],
        ];

        $submission->setPayload($payload);

        $this->assertSame($payload, $submission->getPayload());
        $this->assertSame('John', $submission->getPayload()['user']['name']);
    }

    public function testSetAndGetSubmittedAt(): void
    {
        $submission = new FormSubmission();
        $dateTime = new \DateTime('2024-01-15 10:30:00');

        $result = $submission->setSubmittedAt($dateTime);

        $this->assertSame($dateTime, $submission->getSubmittedAt());
        $this->assertSame($submission, $result);
    }

    public function testSubmittedAtCanBeDateTimeImmutable(): void
    {
        $submission = new FormSubmission();
        $dateTime = new \DateTimeImmutable('2024-01-15 10:30:00');

        $submission->setSubmittedAt($dateTime);

        $this->assertSame($dateTime, $submission->getSubmittedAt());
    }

    public function testSetAndGetForm(): void
    {
        $submission = new FormSubmission();
        $form = new FormDefinition();

        $result = $submission->setForm($form);

        $this->assertSame($form, $submission->getForm());
        $this->assertSame($submission, $result);
    }

    public function testFormCanBeNull(): void
    {
        $submission = new FormSubmission();
        $form = new FormDefinition();

        $submission->setForm($form);
        $submission->setForm(null);

        $this->assertNull($submission->getForm());
    }

    public function testGetNotificationPayloadIncludesPayload(): void
    {
        $submission = new FormSubmission();
        $payload = ['email' => 'test@example.com', 'name' => 'John'];
        $submission->setPayload($payload);
        $submission->setSubmittedAt(new \DateTime('2024-01-15 10:30:00'));

        $notificationPayload = $submission->getNotificationPayload();

        $this->assertSame('test@example.com', $notificationPayload['email']);
        $this->assertSame('John', $notificationPayload['name']);
    }

    public function testGetNotificationPayloadIncludesSubmittedAt(): void
    {
        $submission = new FormSubmission();
        $submission->setPayload([]);
        $submission->setSubmittedAt(new \DateTime('2024-01-15 10:30:00'));

        $notificationPayload = $submission->getNotificationPayload();

        $this->assertArrayHasKey('submittedAt', $notificationPayload);
    }

    public function testGetNotificationPayloadFormatsDateCorrectly(): void
    {
        $submission = new FormSubmission();
        $submission->setPayload([]);
        $submission->setSubmittedAt(new \DateTime('2024-06-20 14:45:30'));

        $notificationPayload = $submission->getNotificationPayload();

        $this->assertSame('2024-06-20 14:45:30', $notificationPayload['submittedAt']);
    }

    public function testGetNotificationPayloadMergesPayloadAndSubmittedAt(): void
    {
        $submission = new FormSubmission();
        $payload = ['field1' => 'value1', 'field2' => 'value2'];
        $submission->setPayload($payload);
        $submission->setSubmittedAt(new \DateTime('2024-01-01 00:00:00'));

        $notificationPayload = $submission->getNotificationPayload();

        $this->assertCount(3, $notificationPayload);
        $this->assertSame('value1', $notificationPayload['field1']);
        $this->assertSame('value2', $notificationPayload['field2']);
        $this->assertSame('2024-01-01 00:00:00', $notificationPayload['submittedAt']);
    }

    public function testGetNotificationPayloadOverwritesPayloadSubmittedAtKey(): void
    {
        $submission = new FormSubmission();
        $payload = ['submittedAt' => 'should be overwritten'];
        $submission->setPayload($payload);
        $submission->setSubmittedAt(new \DateTime('2024-12-31 23:59:59'));

        $notificationPayload = $submission->getNotificationPayload();

        $this->assertSame('2024-12-31 23:59:59', $notificationPayload['submittedAt']);
    }

    public function testFluentSetters(): void
    {
        $submission = new FormSubmission();
        $form = new FormDefinition();
        $dateTime = new \DateTime();

        $result = $submission
            ->setPayload(['test' => 'data'])
            ->setSubmittedAt($dateTime)
            ->setForm($form);

        $this->assertSame($submission, $result);
    }
}
