<?php

namespace App\Tests\Functional\Security;

use App\Tests\Functional\WebTestCase;

class CsrfProtectionTest extends WebTestCase
{
    public function testLoginFormHasCsrfToken(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $csrfInput = $crawler->filter('input[name="_csrf_token"]');
        $this->assertCount(1, $csrfInput);
        $this->assertNotEmpty($csrfInput->attr('value'));
    }

    public function testLoginWithoutCsrfTokenFails(): void
    {
        $this->createUser('test@example.com', 'Test User', 'password123');

        $this->client->request('POST', '/login', [
            '_username' => 'test@example.com',
            '_password' => 'password123',
        ]);

        $this->assertResponseRedirects('/login');
    }

    public function testLoginWithInvalidCsrfTokenFails(): void
    {
        $this->createUser('test@example.com', 'Test User', 'password123');

        $this->client->request('POST', '/login', [
            '_username' => 'test@example.com',
            '_password' => 'password123',
            '_csrf_token' => 'invalid_token_123',
        ]);

        $this->assertResponseRedirects('/login');
    }

    public function testSettingsFormWithInvalidCsrfReturns422(): void
    {
        $this->createAndLoginUser();

        $this->client->request('POST', '/dashboard/settings', [
            'account_settings' => [
                'smtpHost' => 'smtp.test.com',
                '_token' => 'invalid_csrf_token',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testFormDeleteWithInvalidCsrfFails(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        $this->client->request('POST', '/dashboard/forms/' . $form->getId() . '/delete', [
            '_token' => 'invalid_csrf_token',
        ]);

        $this->assertResponseRedirects();

        $this->clearEntityManager();
        $formStillExists = $this->entityManager->find(\App\Entity\FormDefinition::class, $form->getId());
        $this->assertNotNull($formStillExists, 'Form should not be deleted with invalid CSRF token');
    }

    public function testFormDeleteWithValidCsrfSucceeds(): void
    {
        $user = $this->createAndLoginUser();
        $form = $this->createFormDefinition($user, 'Test Form');

        // Go to the forms list page to get the actual CSRF token from the delete form
        $crawler = $this->client->request('GET', '/dashboard/forms');

        // Extract CSRF token from the delete form
        $deleteForm = $crawler->filter('form[action*="/delete"]')->first();
        $token = $deleteForm->filter('input[name="_token"]')->attr('value');

        $this->client->request('POST', '/dashboard/forms/' . $form->getId() . '/delete', [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/dashboard/forms');

        $this->clearEntityManager();
        $formDeleted = $this->entityManager->find(\App\Entity\FormDefinition::class, $form->getId());
        $this->assertNull($formDeleted, 'Form should be deleted with valid CSRF token');
    }
}
