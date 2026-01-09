<?php

namespace App\Tests\Functional\Controller\Dashboard;

use App\Entity\Settings\AccountSettings;
use App\Tests\Functional\WebTestCase;

class SettingsControllerTest extends WebTestCase
{
    public function testSettingsPageRequiresAuthentication(): void
    {
        $this->client->request('GET', '/dashboard/settings');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertRouteSame('app_security_login');
    }

    public function testSettingsPageDisplaysForm(): void
    {
        $this->createAndLoginUser();

        $this->client->request('GET', '/dashboard/settings');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSettingsFormSubmissionSavesData(): void
    {
        $user = $this->createAndLoginUser();

        $crawler = $this->client->request('GET', '/dashboard/settings');

        $form = $crawler->selectButton('Save')->form([
            'account_settings[smtpHost]' => 'smtp.test.com',
            'account_settings[smtpPort]' => '587',
            'account_settings[smtpUser]' => 'testuser',
            'account_settings[smtpPassword]' => 'testpass',
            'account_settings[emailFromAddress]' => 'noreply@test.com',
            'account_settings[emailFromName]' => 'Test Sender',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/dashboard/settings');

        $this->clearEntityManager();

        $settings = $this->entityManager->getRepository(AccountSettings::class)
            ->findOneBy(['owner' => $user->getId()]);

        $this->assertNotNull($settings);
        $this->assertSame('smtp.test.com', $settings->getSmtpHost());
        $this->assertSame(587, $settings->getSmtpPort());
    }

    public function testSettingsFormCsrfProtection(): void
    {
        $this->createAndLoginUser();

        $this->client->request('POST', '/dashboard/settings', [
            'account_settings' => [
                'smtpHost' => 'smtp.test.com',
                '_token' => 'invalid_token',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testSettingsDisplaysCurrentValues(): void
    {
        $user = $this->createAndLoginUser();
        $settings = $this->createAccountSettings($user);
        $settings->setSmtpHost('existing.smtp.com');
        $settings->setSmtpPort(465);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/dashboard/settings');

        $this->assertResponseIsSuccessful();
        $this->assertInputValueSame('account_settings[smtpHost]', 'existing.smtp.com');
        $this->assertInputValueSame('account_settings[smtpPort]', '465');
    }

    public function testSettingsCreatesNewIfNotExists(): void
    {
        $user = $this->createAndLoginUser();

        $crawler = $this->client->request('GET', '/dashboard/settings');

        $form = $crawler->selectButton('Save')->form([
            'account_settings[smtpHost]' => 'new.smtp.com',
            'account_settings[smtpPort]' => '25',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects();

        $this->clearEntityManager();

        $settings = $this->entityManager->getRepository(AccountSettings::class)
            ->findOneBy(['owner' => $user->getId()]);

        $this->assertNotNull($settings);
        $this->assertSame('new.smtp.com', $settings->getSmtpHost());
    }

    public function testSettingsRedirectsOnSuccess(): void
    {
        $this->createAndLoginUser();

        $crawler = $this->client->request('GET', '/dashboard/settings');

        $form = $crawler->selectButton('Save')->form([
            'account_settings[smtpHost]' => 'smtp.example.com',
            'account_settings[smtpPort]' => '587',
        ]);

        $this->client->submit($form);

        // Should redirect back to settings page after successful save
        $this->assertResponseRedirects('/dashboard/settings');
    }
}
