<?php

namespace App\Tests\Unit\Dto;

use App\Dto\FormSubmissionDto;
use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use PHPUnit\Framework\TestCase;

class FormSubmissionDtoTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $payload = ['email' => 'test@example.com', 'message' => 'Hello'];
        $dto = new FormSubmissionDto(
            id: 123,
            payload: $payload,
            formId: 456
        );

        $this->assertSame(123, $dto->id);
        $this->assertSame($payload, $dto->payload);
        $this->assertSame(456, $dto->formId);
    }

    public function testIdPropertyIsAccessible(): void
    {
        $dto = new FormSubmissionDto(999, [], 1);

        $this->assertSame(999, $dto->id);
    }

    public function testPayloadPropertyIsAccessible(): void
    {
        $payload = ['field1' => 'value1', 'field2' => 'value2'];
        $dto = new FormSubmissionDto(1, $payload, 1);

        $this->assertSame($payload, $dto->payload);
    }

    public function testFormIdPropertyIsAccessible(): void
    {
        $dto = new FormSubmissionDto(1, [], 777);

        $this->assertSame(777, $dto->formId);
    }

    public function testEmptyPayloadIsAllowed(): void
    {
        $dto = new FormSubmissionDto(1, [], 1);

        $this->assertSame([], $dto->payload);
    }

    public function testPayloadWithNestedArrays(): void
    {
        $payload = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
            'items' => ['item1', 'item2', 'item3'],
        ];
        $dto = new FormSubmissionDto(1, $payload, 1);

        $this->assertSame($payload, $dto->payload);
        $this->assertSame('John', $dto->payload['user']['name']);
    }

    public function testFromSubmissionFactoryCreatesDto(): void
    {
        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getId')->willReturn(456);

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getId')->willReturn(123);
        $submission->method('getPayload')->willReturn(['email' => 'test@test.com']);
        $submission->method('getForm')->willReturn($formDefinition);

        $dto = FormSubmissionDto::fromSubmission($submission);

        $this->assertInstanceOf(FormSubmissionDto::class, $dto);
    }

    public function testFromSubmissionExtractsIdCorrectly(): void
    {
        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getId')->willReturn(1);

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getId')->willReturn(42);
        $submission->method('getPayload')->willReturn([]);
        $submission->method('getForm')->willReturn($formDefinition);

        $dto = FormSubmissionDto::fromSubmission($submission);

        $this->assertSame(42, $dto->id);
    }

    public function testFromSubmissionExtractsPayloadCorrectly(): void
    {
        $expectedPayload = ['name' => 'Jane', 'subject' => 'Test'];

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getId')->willReturn(1);

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getId')->willReturn(1);
        $submission->method('getPayload')->willReturn($expectedPayload);
        $submission->method('getForm')->willReturn($formDefinition);

        $dto = FormSubmissionDto::fromSubmission($submission);

        $this->assertSame($expectedPayload, $dto->payload);
    }

    public function testFromSubmissionExtractsFormIdCorrectly(): void
    {
        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getId')->willReturn(789);

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getId')->willReturn(1);
        $submission->method('getPayload')->willReturn([]);
        $submission->method('getForm')->willReturn($formDefinition);

        $dto = FormSubmissionDto::fromSubmission($submission);

        $this->assertSame(789, $dto->formId);
    }
}
