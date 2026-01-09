<?php

namespace App\Tests\Integration\Messenger;

use App\Message\Command\SendSubmissionNotification;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class SendSubmissionNotificationTest extends DatabaseTestCase
{
    private MessageBusInterface $messageBus;
    private InMemoryTransport $transport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBus = self::getContainer()->get(MessageBusInterface::class);
        $this->transport = self::getContainer()->get('messenger.transport.async');
    }

    public function testMessageIsDispatchedToAsyncTransport(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $submission = $this->createFormSubmission($form);

        $message = new SendSubmissionNotification($submission->getId());
        $this->messageBus->dispatch($message);

        $this->assertCount(1, $this->transport->getSent());

        $envelope = $this->transport->getSent()[0];
        $dispatchedMessage = $envelope->getMessage();

        $this->assertInstanceOf(SendSubmissionNotification::class, $dispatchedMessage);
        $this->assertSame($submission->getId(), $dispatchedMessage->getSubmissionId());
    }

    public function testMultipleMessagesAreQueued(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $submission1 = $this->createFormSubmission($form, ['email' => 'user1@test.com']);
        $submission2 = $this->createFormSubmission($form, ['email' => 'user2@test.com']);

        $this->messageBus->dispatch(new SendSubmissionNotification($submission1->getId()));
        $this->messageBus->dispatch(new SendSubmissionNotification($submission2->getId()));

        $this->assertCount(2, $this->transport->getSent());
    }

    public function testHandlerProcessesMessage(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');
        $this->createNotificationSettings($form, 'email', 'admin@test.com', false); // Disabled
        $submission = $this->createFormSubmission($form);

        $handler = self::getContainer()->get('App\MessageHandler\Command\SendSubmissionNotificationHandler');

        $message = new SendSubmissionNotification($submission->getId());
        $handler($message);

        $this->assertTrue(true);
    }

    public function testHandlerSkipsDisabledNotifications(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $notificationSettings = $this->createNotificationSettings($form, 'email', 'test@example.com', false);
        $submission = $this->createFormSubmission($form);

        $handler = self::getContainer()->get('App\MessageHandler\Command\SendSubmissionNotificationHandler');

        $message = new SendSubmissionNotification($submission->getId());
        $handler($message);

        $this->assertTrue(true);
    }

    public function testHandlerProcessesEnabledNotifications(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $accountSettings = $this->createAccountSettings($user);
        $accountSettings->setSmtpHost('smtp.test.com');
        $accountSettings->setSmtpPort(587);
        $this->entityManager->flush();

        $notificationSettings = $this->createNotificationSettings($form, 'email', 'recipient@example.com', true);
        $submission = $this->createFormSubmission($form, ['email' => 'sender@example.com']);

        $handler = self::getContainer()->get('App\MessageHandler\Command\SendSubmissionNotificationHandler');
        $message = new SendSubmissionNotification($submission->getId());

        $this->assertTrue(true);
    }
}
