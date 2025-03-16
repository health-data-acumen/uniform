<?php

namespace App\Service\Notification;

use App\Entity\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['app.notification.provider'])]
interface ProviderInterface
{
    public static function getName(): string;

    public static function getPriority(): int;

    public function triggerNotification(FormSubmission $formSubmission): void;

    public function getConfigurationForm(): string;

    public function checkRequirements(): bool;

    public function getRequirementsMessage(): ?string;
}
