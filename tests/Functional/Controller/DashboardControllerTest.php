<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    public function testDashboardRedirectsToFormList(): void
    {
        $user = $this->createAndLoginUser();

        $this->client->request('GET', '/dashboard');

        $this->assertResponseRedirects('/dashboard/forms');
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $this->client->request('GET', '/dashboard');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_security_login');
    }

    public function testDashboardAccessibleForAuthenticatedUser(): void
    {
        $user = $this->createAndLoginUser();

        $this->client->request('GET', '/dashboard');

        $this->assertResponseRedirects('/dashboard/forms');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testDashboardRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/dashboard');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
    }
}
