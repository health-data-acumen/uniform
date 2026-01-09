<?php

namespace App\Tests\Unit\Service\FormEndpoint;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Service\FormEndpoint\SubmissionService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class SubmissionServiceTest extends TestCase
{
    public function testSaveSubmissionCreatesNewSubmission(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(FormSubmission::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $request = $this->createMockRequest(['email' => 'test@example.com']);
        $endpoint = new FormDefinition();

        $service = new SubmissionService($entityManager);
        $result = $service->saveSubmission($endpoint, $request);

        $this->assertInstanceOf(FormSubmission::class, $result);
    }

    public function testSaveSubmissionExtractsPayloadFromRequest(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $payload = ['email' => 'test@example.com', 'message' => 'Hello'];

        $request = $this->createMockRequest($payload);
        $endpoint = new FormDefinition();

        $service = new SubmissionService($entityManager);
        $result = $service->saveSubmission($endpoint, $request);

        $this->assertSame($payload, $result->getPayload());
    }

    public function testSaveSubmissionAssociatesWithFormDefinition(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $request = $this->createMockRequest([]);
        $endpoint = new FormDefinition();
        $endpoint->setName('Test Form');

        $service = new SubmissionService($entityManager);
        $result = $service->saveSubmission($endpoint, $request);

        $this->assertSame($endpoint, $result->getForm());
    }

    public function testSaveSubmissionFlushesEntityManager(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $request = $this->createMockRequest([]);
        $endpoint = new FormDefinition();

        $service = new SubmissionService($entityManager);
        $service->saveSubmission($endpoint, $request);
    }

    public function testSaveSubmissionReturnsFormSubmission(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $request = $this->createMockRequest(['field' => 'value']);
        $endpoint = new FormDefinition();

        $service = new SubmissionService($entityManager);
        $result = $service->saveSubmission($endpoint, $request);

        $this->assertInstanceOf(FormSubmission::class, $result);
        $this->assertSame($endpoint, $result->getForm());
        $this->assertSame(['field' => 'value'], $result->getPayload());
    }

    public function testSaveSubmissionWithEmptyPayload(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $request = $this->createMockRequest([]);
        $endpoint = new FormDefinition();

        $service = new SubmissionService($entityManager);
        $result = $service->saveSubmission($endpoint, $request);

        $this->assertSame([], $result->getPayload());
    }

    public function testGetSubmittedFieldsReturnsDistinctKeys(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')
            ->willReturn([
                ['key' => 'email'],
                ['key' => 'name'],
                ['key' => 'message'],
            ]);

        $connection = $this->createMock(Connection::class);
        $connection->method('executeQuery')
            ->willReturn($result);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')
            ->willReturn($connection);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $service = new SubmissionService($entityManager);
        $keys = $service->getSubmittedFields($endpoint);

        $this->assertSame(['email', 'name', 'message'], $keys);
    }

    public function testGetSubmittedFieldsReturnsEmptyForNoSubmissions(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')
            ->willReturn([]);

        $connection = $this->createMock(Connection::class);
        $connection->method('executeQuery')
            ->willReturn($result);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')
            ->willReturn($connection);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $service = new SubmissionService($entityManager);
        $keys = $service->getSubmittedFields($endpoint);

        $this->assertSame([], $keys);
    }

    public function testGetPriorityFormFieldsReturnsPriorityFields(): void
    {
        $service = $this->createServiceWithSubmittedFields(['email', 'name', 'subject', 'message', 'phone']);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $result = $service->getPriorityFormFields($endpoint);

        $this->assertSame(['email', 'name'], $result);
    }

    public function testGetPriorityFormFieldsLimitsToMax(): void
    {
        $service = $this->createServiceWithSubmittedFields(['email', 'name', 'subject', 'message']);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $result1 = $service->getPriorityFormFields($endpoint, 1);
        $this->assertCount(1, $result1);
        $this->assertSame(['email'], $result1);
    }

    public function testGetPriorityFormFieldsLimitsToMaxWithThree(): void
    {
        $service = $this->createServiceWithSubmittedFields(['email', 'name', 'subject', 'message']);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $result3 = $service->getPriorityFormFields($endpoint, 3);
        $this->assertCount(3, $result3);
        $this->assertSame(['email', 'name', 'subject'], $result3);
    }

    public function testGetPriorityFormFieldsReturnsFallbackField(): void
    {
        $service = $this->createServiceWithSubmittedFields(['custom_field', 'another_field']);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $result = $service->getPriorityFormFields($endpoint);

        $this->assertSame(['custom_field'], $result);
    }

    public function testGetPriorityFormFieldsReturnsEmptyWhenNoFields(): void
    {
        $service = $this->createServiceWithSubmittedFields([]);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $result = $service->getPriorityFormFields($endpoint);

        $this->assertSame([], $result);
    }

    public function testGetPriorityFormFieldsRespectsPriorityOrder(): void
    {
        $service = $this->createServiceWithSubmittedFields(['message', 'subject', 'name']);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $result = $service->getPriorityFormFields($endpoint, 3);

        $this->assertSame(['name', 'subject', 'message'], $result);
    }

    public function testGetPriorityFormFieldsWithOnlyMessage(): void
    {
        $service = $this->createServiceWithSubmittedFields(['message']);

        $endpoint = $this->createMock(FormDefinition::class);
        $endpoint->method('getId')->willReturn(1);

        $result = $service->getPriorityFormFields($endpoint);

        $this->assertSame(['message'], $result);
    }

    private function createMockRequest(array $payload): Request
    {
        $request = $this->createMock(Request::class);
        $inputBag = new InputBag($payload);
        $request->method('getPayload')->willReturn($inputBag);

        return $request;
    }

    private function createServiceWithSubmittedFields(array $fields): SubmissionService
    {
        $resultStatement = $this->createMock(Result::class);
        $resultStatement->method('fetchAllAssociative')
            ->willReturn(array_map(fn ($key) => ['key' => $key], $fields));

        $connection = $this->createMock(Connection::class);
        $connection->method('executeQuery')->willReturn($resultStatement);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        return new SubmissionService($entityManager);
    }
}
