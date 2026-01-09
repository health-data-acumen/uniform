<?php

namespace App\Tests\Integration\Repository;

use App\Dto\FormEndpointDto;
use App\Entity\FormDefinition;
use App\Repository\FormDefinitionRepository;
use App\Tests\Integration\DatabaseTestCase;

class FormDefinitionRepositoryTest extends DatabaseTestCase
{
    private FormDefinitionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(FormDefinition::class);
    }

    public function testGetEndpointsReturnsEmptyArrayWhenNoForms(): void
    {
        $endpoints = $this->repository->getEndpoints();

        $this->assertIsArray($endpoints);
        $this->assertEmpty($endpoints);
    }

    public function testGetEndpointsReturnsDtoWithCorrectProperties(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Contact Form');

        $endpoints = $this->repository->getEndpoints();

        $this->assertCount(1, $endpoints);
        $this->assertInstanceOf(FormEndpointDto::class, $endpoints[0]);
        $this->assertSame((string) $form->getId(), $endpoints[0]->id);
        $this->assertSame('Contact Form', $endpoints[0]->name);
        $this->assertTrue($endpoints[0]->isActive);
        $this->assertSame(0, $endpoints[0]->submissionsCount);
    }

    public function testGetEndpointsCountsSubmissionsCorrectly(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Form with Submissions');

        $this->createFormSubmission($form, ['email' => 'user1@example.com']);
        $this->createFormSubmission($form, ['email' => 'user2@example.com']);
        $this->createFormSubmission($form, ['email' => 'user3@example.com']);

        $endpoints = $this->repository->getEndpoints();

        $this->assertCount(1, $endpoints);
        $this->assertSame(3, $endpoints[0]->submissionsCount);
    }

    public function testGetEndpointsWithZeroSubmissions(): void
    {
        $user = $this->createUser();
        $this->createFormDefinition($user, 'Empty Form');

        $endpoints = $this->repository->getEndpoints();

        $this->assertCount(1, $endpoints);
        $this->assertSame(0, $endpoints[0]->submissionsCount);
    }

    public function testGetEndpointsWithMultipleForms(): void
    {
        $user = $this->createUser();
        $form1 = $this->createFormDefinition($user, 'Form A');
        $form2 = $this->createFormDefinition($user, 'Form B');
        $form3 = $this->createFormDefinition($user, 'Form C');

        $this->createFormSubmission($form1);
        $this->createFormSubmission($form2);
        $this->createFormSubmission($form2);

        $endpoints = $this->repository->getEndpoints();

        $this->assertCount(3, $endpoints);

        $endpointsByName = [];
        foreach ($endpoints as $endpoint) {
            $endpointsByName[$endpoint->name] = $endpoint;
        }

        $this->assertSame(1, $endpointsByName['Form A']->submissionsCount);
        $this->assertSame(2, $endpointsByName['Form B']->submissionsCount);
        $this->assertSame(0, $endpointsByName['Form C']->submissionsCount);
    }

    public function testGetEndpointsIncludesDisabledForms(): void
    {
        $user = $this->createUser();
        $this->createFormDefinition($user, 'Enabled Form', null, true);
        $this->createFormDefinition($user, 'Disabled Form', null, false);

        $endpoints = $this->repository->getEndpoints();

        $this->assertCount(2, $endpoints);

        $endpointsByName = [];
        foreach ($endpoints as $endpoint) {
            $endpointsByName[$endpoint->name] = $endpoint;
        }

        $this->assertTrue($endpointsByName['Enabled Form']->isActive);
        $this->assertFalse($endpointsByName['Disabled Form']->isActive);
    }

    public function testFindByUidReturnsCorrectForm(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');
        $uid = $form->getUid();

        $found = $this->repository->findOneBy(['uid' => $uid]);

        $this->assertNotNull($found);
        $this->assertSame($form->getId(), $found->getId());
        $this->assertSame('Test Form', $found->getName());
    }

    public function testFindByUidReturnsNullForNonExistentUid(): void
    {
        $found = $this->repository->findOneBy(['uid' => '00000000-0000-0000-0000-000000000000']);

        $this->assertNull($found);
    }
}
