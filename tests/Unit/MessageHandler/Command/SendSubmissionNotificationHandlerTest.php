<?php

namespace App\Tests\Unit\MessageHandler\Command;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Entity\Settings\NotificationSettings;
use App\Message\Command\SendSubmissionNotification;
use App\MessageHandler\Command\SendSubmissionNotificationHandler;
use App\Repository\FormSubmissionRepository;
use App\Service\Notification\ChannelInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SendSubmissionNotificationHandlerTest extends TestCase
{
    public function testInvokeTriggersEnabledNotificationChannels(): void
    {
        $notificationSettings = new NotificationSettings();
        $notificationSettings->setEnabled(true);
        $notificationSettings->setType('email');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getNotificationSettings')
            ->willReturn(new ArrayCollection([$notificationSettings]));

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getForm')->willReturn($formDefinition);

        $repository = $this->createMock(FormSubmissionRepository::class);
        $repository->method('find')->with(123)->willReturn($submission);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('checkRequirements')->willReturn(true);
        $channel->expects($this->once())
            ->method('triggerNotification')
            ->with($submission, $notificationSettings);

        $channels = ['email' => $channel];

        $handler = new SendSubmissionNotificationHandler($repository, new \ArrayIterator($channels));

        $message = new SendSubmissionNotification(123);
        $handler($message);
    }

    public function testInvokeSkipsDisabledNotifications(): void
    {
        $enabledNotification = new NotificationSettings();
        $enabledNotification->setEnabled(true);
        $enabledNotification->setType('email');

        $disabledNotification = new NotificationSettings();
        $disabledNotification->setEnabled(false);
        $disabledNotification->setType('webhook');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getNotificationSettings')
            ->willReturn(new ArrayCollection([$enabledNotification, $disabledNotification]));

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getForm')->willReturn($formDefinition);

        $repository = $this->createMock(FormSubmissionRepository::class);
        $repository->method('find')->willReturn($submission);

        $emailChannel = $this->createMock(ChannelInterface::class);
        $emailChannel->method('checkRequirements')->willReturn(true);
        $emailChannel->expects($this->once())->method('triggerNotification');

        $webhookChannel = $this->createMock(ChannelInterface::class);
        $webhookChannel->expects($this->never())->method('triggerNotification');

        $channels = [
            'email' => $emailChannel,
            'webhook' => $webhookChannel,
        ];

        $handler = new SendSubmissionNotificationHandler($repository, new \ArrayIterator($channels));

        $message = new SendSubmissionNotification(1);
        $handler($message);
    }

    public function testInvokeLogsErrorForUnregisteredChannel(): void
    {
        $notificationSettings = new NotificationSettings();
        $notificationSettings->setEnabled(true);
        $notificationSettings->setType('unknown_channel');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getNotificationSettings')
            ->willReturn(new ArrayCollection([$notificationSettings]));

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getForm')->willReturn($formDefinition);

        $repository = $this->createMock(FormSubmissionRepository::class);
        $repository->method('find')->willReturn($submission);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('unknown_channel'));

        $handler = new SendSubmissionNotificationHandler($repository, new \ArrayIterator([]));
        $handler->setLogger($logger);

        $message = new SendSubmissionNotification(1);
        $handler($message);
    }

    public function testInvokeChecksChannelRequirementsBeforeTriggering(): void
    {
        $notificationSettings = new NotificationSettings();
        $notificationSettings->setEnabled(true);
        $notificationSettings->setType('email');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getNotificationSettings')
            ->willReturn(new ArrayCollection([$notificationSettings]));

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getForm')->willReturn($formDefinition);

        $repository = $this->createMock(FormSubmissionRepository::class);
        $repository->method('find')->willReturn($submission);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())->method('checkRequirements')->willReturn(true);
        $channel->expects($this->once())->method('triggerNotification');

        $channels = ['email' => $channel];

        $handler = new SendSubmissionNotificationHandler($repository, new \ArrayIterator($channels));

        $message = new SendSubmissionNotification(1);
        $handler($message);
    }

    public function testInvokeDoesNotTriggerWhenRequirementsNotMet(): void
    {
        $notificationSettings = new NotificationSettings();
        $notificationSettings->setEnabled(true);
        $notificationSettings->setType('email');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getNotificationSettings')
            ->willReturn(new ArrayCollection([$notificationSettings]));

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getForm')->willReturn($formDefinition);

        $repository = $this->createMock(FormSubmissionRepository::class);
        $repository->method('find')->willReturn($submission);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('checkRequirements')->willReturn(false);
        $channel->expects($this->never())->method('triggerNotification');

        $channels = ['email' => $channel];

        $handler = new SendSubmissionNotificationHandler($repository, new \ArrayIterator($channels));

        $message = new SendSubmissionNotification(1);
        $handler($message);
    }

    public function testInvokeHandlesMultipleEnabledChannels(): void
    {
        $emailNotification = new NotificationSettings();
        $emailNotification->setEnabled(true);
        $emailNotification->setType('email');

        $webhookNotification = new NotificationSettings();
        $webhookNotification->setEnabled(true);
        $webhookNotification->setType('webhook');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getNotificationSettings')
            ->willReturn(new ArrayCollection([$emailNotification, $webhookNotification]));

        $submission = $this->createMock(FormSubmission::class);
        $submission->method('getForm')->willReturn($formDefinition);

        $repository = $this->createMock(FormSubmissionRepository::class);
        $repository->method('find')->willReturn($submission);

        $emailChannel = $this->createMock(ChannelInterface::class);
        $emailChannel->method('checkRequirements')->willReturn(true);
        $emailChannel->expects($this->once())->method('triggerNotification');

        $webhookChannel = $this->createMock(ChannelInterface::class);
        $webhookChannel->method('checkRequirements')->willReturn(true);
        $webhookChannel->expects($this->once())->method('triggerNotification');

        $channels = [
            'email' => $emailChannel,
            'webhook' => $webhookChannel,
        ];

        $handler = new SendSubmissionNotificationHandler($repository, new \ArrayIterator($channels));

        $message = new SendSubmissionNotification(1);
        $handler($message);
    }
}
