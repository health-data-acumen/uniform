<?php

namespace App\Tests\Integration\Repository;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Repository\FormSubmissionRepository;
use App\Tests\Integration\DatabaseTestCase;
use Doctrine\ORM\QueryBuilder;

class FormSubmissionRepositoryTest extends DatabaseTestCase
{
    private FormSubmissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(FormSubmission::class);
    }

    public function testSavePersistsSubmission(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload(['email' => 'test@example.com']);
        $submission->setSubmittedAt(new \DateTimeImmutable());

        $saved = $this->repository->save($submission);

        $this->assertNotNull($saved->getId());
        $this->assertSame(['email' => 'test@example.com'], $saved->getPayload());
    }

    public function testSaveWithFlushFalse(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload(['name' => 'John']);
        $submission->setSubmittedAt(new \DateTimeImmutable());

        $this->repository->save($submission, false);

        $this->assertNull($submission->getId());

        $this->entityManager->flush();

        $this->assertNotNull($submission->getId());
    }

    public function testSaveReturnsSubmission(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload(['message' => 'Hello']);
        $submission->setSubmittedAt(new \DateTimeImmutable());

        $result = $this->repository->save($submission);

        $this->assertSame($submission, $result);
    }

    public function testBuildSelectQueryReturnsQueryBuilder(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $queryBuilder = $this->repository->buildSelectQuery($form);

        $this->assertInstanceOf(QueryBuilder::class, $queryBuilder);
    }

    public function testBuildSelectQueryFiltersByForm(): void
    {
        $user = $this->createUser();
        $form1 = $this->createFormDefinition($user, 'Form 1');
        $form2 = $this->createFormDefinition($user, 'Form 2');

        $submission1 = $this->createFormSubmission($form1, ['source' => 'form1']);
        $submission2 = $this->createFormSubmission($form2, ['source' => 'form2']);
        $submission3 = $this->createFormSubmission($form1, ['source' => 'form1-2']);

        $results = $this->repository->buildSelectQuery($form1)->getQuery()->getResult();

        $this->assertCount(2, $results);

        $ids = array_map(fn ($s) => $s->getId(), $results);
        $this->assertContains($submission1->getId(), $ids);
        $this->assertContains($submission3->getId(), $ids);
        $this->assertNotContains($submission2->getId(), $ids);
    }

    public function testBuildSelectQueryOrdersByCreatedAtDesc(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        // Create submissions with different createdAt timestamps
        // Note: The TimestampableEntity trait sets createdAt on persist
        // We need to manually set createdAt after creation using DateTime (not DateTimeImmutable)
        $oldest = new FormSubmission();
        $oldest->setForm($form);
        $oldest->setPayload(['order' => 1]);
        $oldest->setSubmittedAt(new \DateTimeImmutable('-3 days'));
        $oldest->setCreatedAt(new \DateTime('-3 days'));
        $this->entityManager->persist($oldest);

        $middle = new FormSubmission();
        $middle->setForm($form);
        $middle->setPayload(['order' => 2]);
        $middle->setSubmittedAt(new \DateTimeImmutable('-2 days'));
        $middle->setCreatedAt(new \DateTime('-2 days'));
        $this->entityManager->persist($middle);

        $newest = new FormSubmission();
        $newest->setForm($form);
        $newest->setPayload(['order' => 3]);
        $newest->setSubmittedAt(new \DateTimeImmutable('-1 day'));
        $newest->setCreatedAt(new \DateTime('-1 day'));
        $this->entityManager->persist($newest);

        $this->entityManager->flush();

        $results = $this->repository->buildSelectQuery($form)->getQuery()->getResult();

        $this->assertCount(3, $results);
        $this->assertSame($newest->getId(), $results[0]->getId());
        $this->assertSame($middle->getId(), $results[1]->getId());
        $this->assertSame($oldest->getId(), $results[2]->getId());
    }

    public function testBuildSelectQueryJoinsFormEntity(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form Name');
        $this->createFormSubmission($form);

        $this->clearEntityManager();

        $results = $this->repository->buildSelectQuery($form)->getQuery()->getResult();

        $this->assertCount(1, $results);
        $this->assertSame('Test Form Name', $results[0]->getForm()->getName());
    }

    public function testFindByFormReturnsCorrectSubmissions(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $submission1 = $this->createFormSubmission($form, ['field' => 'value1']);
        $submission2 = $this->createFormSubmission($form, ['field' => 'value2']);

        $found = $this->repository->findBy(['form' => $form]);

        $this->assertCount(2, $found);
    }

    public function testFindByFormReturnsEmptyForFormWithNoSubmissions(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $found = $this->repository->findBy(['form' => $form]);

        $this->assertEmpty($found);
    }
}
