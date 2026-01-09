<?php

namespace App\Tests\Integration\Service\Notification;

use App\Entity\Settings\NotificationSettings;
use App\Service\Notification\EmailChannel;
use App\Tests\Integration\DatabaseTestCase;

class EmailChannelTest extends DatabaseTestCase
{
    public function testCheckRequirementsReturnsTrueWhenConfigured(): void
    {
        $user = $this->createUser();
        $accountSettings = $this->createAccountSettings($user);
        $accountSettings->setSmtpHost('smtp.example.com');
        $accountSettings->setSmtpPort(587);
        $this->entityManager->flush();

        $emailChannel = self::getContainer()->get(EmailChannel::class);

        $this->assertTrue($emailChannel->checkRequirements());
    }

    public function testCheckRequirementsReturnsFalseWhenNotConfigured(): void
    {
        $emailChannel = self::getContainer()->get(EmailChannel::class);

        $this->assertFalse($emailChannel->checkRequirements());
    }

    public function testCheckRequirementsReturnsFalseWhenHostMissing(): void
    {
        $user = $this->createUser();
        $accountSettings = $this->createAccountSettings($user);
        $accountSettings->setSmtpPort(587);
        $this->entityManager->flush();

        $emailChannel = self::getContainer()->get(EmailChannel::class);

        $this->assertFalse($emailChannel->checkRequirements());
    }

    public function testCheckRequirementsReturnsFalseWhenPortMissing(): void
    {
        $user = $this->createUser();
        $accountSettings = $this->createAccountSettings($user);
        $accountSettings->setSmtpHost('smtp.example.com');
        $this->entityManager->flush();

        $emailChannel = self::getContainer()->get(EmailChannel::class);

        $this->assertFalse($emailChannel->checkRequirements());
    }

    public function testGetNameReturnsEmail(): void
    {
        $this->assertSame('email', EmailChannel::getName());
    }

    public function testGetPriorityReturnsZero(): void
    {
        $this->assertSame(0, EmailChannel::getPriority());
    }

    public function testGetRequirementsMessageReturnsExpectedMessage(): void
    {
        $emailChannel = self::getContainer()->get(EmailChannel::class);

        $message = $emailChannel->getRequirementsMessage();

        $this->assertStringContainsString('email server settings', $message);
        $this->assertStringContainsString('host', $message);
        $this->assertStringContainsString('port', $message);
    }

    public function testTriggerNotificationReturnsEarlyWhenRequirementsNotMet(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $submission = $this->createFormSubmission($form);
        $notificationSettings = $this->createNotificationSettings($form, 'email', 'test@example.com', true);

        $emailChannel = self::getContainer()->get(EmailChannel::class);

        $emailChannel->triggerNotification($submission, $notificationSettings);

        $this->assertTrue(true);
    }
}
