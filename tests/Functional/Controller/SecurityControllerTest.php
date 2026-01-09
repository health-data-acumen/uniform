<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testLoginWithValidCredentials(): void
    {
        $this->createUser('user@example.com', 'Test User', 'password123');

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'user@example.com',
            '_password' => 'password123',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_dashboard_index');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->createUser('user@example.com', 'Test User', 'password123');

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'user@example.com',
            '_password' => 'wrongpassword',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('[role="alert"]');
    }

    public function testLoginRedirectsAuthenticatedUser(): void
    {
        $user = $this->createAndLoginUser();

        $this->client->request('GET', '/login');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_default_index');
    }

    public function testLogoutRedirectsToLogin(): void
    {
        $user = $this->createAndLoginUser();

        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_security_login');
    }

    public function testLoginFormContainsCsrfToken(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $csrfToken = $crawler->filter('input[name="_csrf_token"]');
        $this->assertCount(1, $csrfToken);
        $this->assertNotEmpty($csrfToken->attr('value'));
    }

    public function testLoginWithEmptyCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => '',
            '_password' => '',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/login');
    }

}
