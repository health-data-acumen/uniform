<?php

namespace App\Tests\Unit\Service\Notification;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Entity\Settings\AccountSettings;
use App\Entity\Settings\NotificationSettings;
use App\Form\Settings\Notification\EmailNotificationType;
use App\Service\AccountSettingsService;
use App\Service\Notification\Email\EmailBuilder;
use App\Service\Notification\EmailChannel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailChannelTest extends TestCase
{
    public function testGetConfigurationFormReturnsEmailNotificationType(): void
    {
        $channel = $this->createEmailChannel();

        $this->assertSame(EmailNotificationType::class, $channel->getConfigurationForm());
    }

    public function testGetNameReturnsEmail(): void
    {
        $this->assertSame('email', EmailChannel::getName());
    }

    public function testGetPriorityReturnsZero(): void
    {
        $this->assertSame(0, EmailChannel::getPriority());
    }

    public function testCheckRequirementsReturnsTrueWhenConfigured(): void
    {
        $accountSettings = (new AccountSettings())
            ->setSmtpHost('smtp.example.com')
            ->setSmtpPort(587);

        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn($accountSettings);

        $channel = $this->createEmailChannel($settingsService);

        $this->assertTrue($channel->checkRequirements());
    }

    public function testCheckRequirementsReturnsFalseWhenHostMissing(): void
    {
        $accountSettings = (new AccountSettings())
            ->setSmtpHost(null)
            ->setSmtpPort(587);

        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn($accountSettings);

        $channel = $this->createEmailChannel($settingsService);

        $this->assertFalse($channel->checkRequirements());
    }

    public function testCheckRequirementsReturnsFalseWhenPortMissing(): void
    {
        $accountSettings = (new AccountSettings())
            ->setSmtpHost('smtp.example.com')
            ->setSmtpPort(null);

        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn($accountSettings);

        $channel = $this->createEmailChannel($settingsService);

        $this->assertFalse($channel->checkRequirements());
    }

    public function testCheckRequirementsReturnsFalseWhenNoSettings(): void
    {
        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn(null);

        $channel = $this->createEmailChannel($settingsService);

        $this->assertFalse($channel->checkRequirements());
    }

    public function testGetRequirementsMessageReturnsExpectedMessage(): void
    {
        $channel = $this->createEmailChannel();

        $message = $channel->getRequirementsMessage();

        $this->assertStringContainsString('email server settings', $message);
        $this->assertStringContainsString('host', $message);
        $this->assertStringContainsString('port', $message);
    }

    public function testTriggerNotificationReturnsEarlyWhenRequirementsNotMet(): void
    {
        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn(null);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('not configured properly'));

        $channel = $this->createEmailChannel($settingsService);
        $channel->setLogger($logger);

        $formSubmission = $this->createMock(FormSubmission::class);
        $notificationSettings = new NotificationSettings();

        $channel->triggerNotification($formSubmission, $notificationSettings);
    }

    public function testTriggerNotificationSendsEmail(): void
    {
        $accountSettings = (new AccountSettings())
            ->setSmtpHost('smtp.example.com')
            ->setSmtpPort(587)
            ->setSmtpUser('user')
            ->setSmtpPassword('pass')
            ->setEmailFromAddress('from@example.com')
            ->setEmailFromName('Test Sender');

        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn($accountSettings);

        $emailBuilder = $this->createRealEmailBuilder();

        $transport = $this->createMock(TransportInterface::class);
        $createTransport = fn () => $transport;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('info');

        $channel = new EmailChannel($settingsService, $createTransport, $emailBuilder);
        $channel->setLogger($logger);

        $formDefinition = new FormDefinition();
        $formDefinition->setName('Test');

        $formSubmission = new FormSubmission();
        $formSubmission->setForm($formDefinition);
        $formSubmission->setPayload(['name' => 'John']);
        $formSubmission->setSubmittedAt(new \DateTimeImmutable());

        $notificationSettings = new NotificationSettings();
        $notificationSettings->setTarget('admin@example.com');

        $channel->triggerNotification($formSubmission, $notificationSettings);
    }

    public function testTriggerNotificationUsesConfiguredSenderAddress(): void
    {
        $accountSettings = (new AccountSettings())
            ->setSmtpHost('smtp.example.com')
            ->setSmtpPort(587)
            ->setEmailFromAddress('configured@example.com')
            ->setEmailFromName('Configured Sender');

        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn($accountSettings);

        $capturedEmail = null;
        $emailBuilder = $this->createRealEmailBuilder();

        $transport = $this->createMock(TransportInterface::class);
        $transport->method('send')->willReturnCallback(function ($message) use (&$capturedEmail) {
            $capturedEmail = $message;
        });
        $createTransport = fn () => $transport;

        $logger = $this->createMock(LoggerInterface::class);

        $channel = new EmailChannel($settingsService, $createTransport, $emailBuilder);
        $channel->setLogger($logger);

        $formDefinition = new FormDefinition();
        $formDefinition->setName('Test');

        $formSubmission = new FormSubmission();
        $formSubmission->setForm($formDefinition);
        $formSubmission->setPayload(['name' => 'John']);
        $formSubmission->setSubmittedAt(new \DateTimeImmutable());

        $notificationSettings = new NotificationSettings();
        $notificationSettings->setTarget('admin@example.com');

        $channel->triggerNotification($formSubmission, $notificationSettings);

        $this->assertNotNull($capturedEmail);
        $fromAddresses = $capturedEmail->getFrom();
        $this->assertCount(1, $fromAddresses);
        $this->assertSame('configured@example.com', $fromAddresses[0]->getAddress());
    }

    public function testTriggerNotificationLogsTransportException(): void
    {
        $accountSettings = (new AccountSettings())
            ->setSmtpHost('smtp.example.com')
            ->setSmtpPort(587);

        $settingsService = $this->createMock(AccountSettingsService::class);
        $settingsService->method('getSettings')->willReturn($accountSettings);

        $emailBuilder = $this->createRealEmailBuilder();

        $transport = $this->createMock(TransportInterface::class);
        $transport->method('send')->willThrowException(new TransportException('Connection failed'));
        $createTransport = fn () => $transport;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('error occurred'),
                $this->arrayHasKey('exception')
            );

        $channel = new EmailChannel($settingsService, $createTransport, $emailBuilder);
        $channel->setLogger($logger);

        $formDefinition = new FormDefinition();
        $formDefinition->setName('Test');

        $formSubmission = new FormSubmission();
        $formSubmission->setForm($formDefinition);
        $formSubmission->setPayload(['name' => 'John']);
        $formSubmission->setSubmittedAt(new \DateTimeImmutable());

        $notificationSettings = new NotificationSettings();
        $notificationSettings->setTarget('admin@example.com');

        $this->expectException(TransportException::class);
        $channel->triggerNotification($formSubmission, $notificationSettings);
    }

    private function createEmailChannel(
        ?AccountSettingsService $settingsService = null,
        ?EmailBuilder $emailBuilder = null
    ): EmailChannel {
        $settingsService = $settingsService ?? $this->createMock(AccountSettingsService::class);
        $emailBuilder = $emailBuilder ?? $this->createRealEmailBuilder();
        $createTransport = fn () => $this->createMock(TransportInterface::class);

        return new EmailChannel($settingsService, $createTransport, $emailBuilder);
    }

    private function createRealEmailBuilder(): EmailBuilder
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(fn ($key) => $key);

        return new EmailBuilder($translator);
    }
}
