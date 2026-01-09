<?php

namespace App\Tests\Functional\Controller;

use App\Entity\FormSubmission;
use App\Tests\Functional\WebTestCase;

class EndpointControllerTest extends WebTestCase
{
    public function testSubmitToValidEndpoint(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Contact Form', null, true);

        $this->client->request('POST', '/e/' . $form->getUid(), [
            'email' => 'test@example.com',
            'message' => 'Hello world',
        ]);

        $this->assertResponseRedirects('/thank-you');
    }

    public function testSubmitToDisabledEndpoint(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Disabled Form', null, false);

        $this->client->request('POST', '/e/' . $form->getUid(), [
            'email' => 'test@example.com',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSubmitToNonExistentEndpoint(): void
    {
        $this->client->request('POST', '/e/00000000-0000-0000-0000-000000000000', [
            'email' => 'test@example.com',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSubmitRedirectsToConfiguredUrl(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Form with Redirect', null, true, 'https://example.com/success');

        $this->client->request('POST', '/e/' . $form->getUid(), [
            'email' => 'test@example.com',
        ]);

        $this->assertResponseRedirects('https://example.com/success');
    }

    public function testSubmitRedirectsToDefaultSuccessPage(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Form without Redirect', null, true, null);

        $this->client->request('POST', '/e/' . $form->getUid(), [
            'email' => 'test@example.com',
        ]);

        $this->assertResponseRedirects('/thank-you');
    }

    public function testSubmitSavesPayloadCorrectly(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Test message',
        ];

        $this->client->request('POST', '/e/' . $form->getUid(), $payload);

        $this->clearEntityManager();

        $submissions = $this->entityManager->getRepository(FormSubmission::class)
            ->findBy(['form' => $form->getId()]);

        $this->assertCount(1, $submissions);
        $this->assertSame($payload, $submissions[0]->getPayload());
    }

    public function testSubmitWithEmptyPayload(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $this->client->request('POST', '/e/' . $form->getUid(), []);

        $this->assertResponseRedirects('/thank-you');

        $this->clearEntityManager();

        $submissions = $this->entityManager->getRepository(FormSubmission::class)
            ->findBy(['form' => $form->getId()]);

        $this->assertCount(1, $submissions);
        $this->assertSame([], $submissions[0]->getPayload());
    }

    public function testGetMethodNotAllowed(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $this->client->request('GET', '/e/' . $form->getUid());

        $this->assertResponseStatusCodeSame(405);
    }

    public function testSubmitIncrementsSubmissionCount(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $this->client->request('POST', '/e/' . $form->getUid(), ['email' => 'first@example.com']);
        $this->client->request('POST', '/e/' . $form->getUid(), ['email' => 'second@example.com']);
        $this->client->request('POST', '/e/' . $form->getUid(), ['email' => 'third@example.com']);

        $this->clearEntityManager();

        $submissions = $this->entityManager->getRepository(FormSubmission::class)
            ->findBy(['form' => $form->getId()]);

        $this->assertCount(3, $submissions);
    }

    public function testSubmitWithJsonPayload(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ];

        $this->client->request(
            'POST',
            '/e/' . $form->getUid(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseRedirects('/thank-you');
    }
}
