<?php

namespace App\Service\Notification;

use App\Entity\FormSubmission;
use App\Form\Settings\Notification\EmailNotificationType;
use App\Service\AccountSettingsService;
use Doctrine\Common\Collections\ArrayCollection;

class EmailProvider implements ProviderInterface
{
    private ?ArrayCollection $settings = null;

    public function __construct(private readonly AccountSettingsService $settingsManager)
    {
    }

    public function triggerNotification(FormSubmission $formSubmission): void
    {
    }

    public function getConfigurationForm(): string
    {
        return EmailNotificationType::class;
    }

    public static function getName(): string
    {
        return 'email';
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function checkRequirements(): bool
    {
        return !$this->getSettings()
            ->filter(fn ($_, string $key) => in_array($key, ['host', 'port'])) // Check only host and port
            ->exists(fn ($key, $value) => !$value);
    }

    private function getSettings(): ArrayCollection
    {
        if (null === $this->settings) {
            $this->settings = new ArrayCollection($this->getEmailServerSettings());
        }

        return $this->settings;
    }

    private function getEmailServerSettings(): array
    {
        $settings = $this->settingsManager->getSettings();

        return [
            'host' => $settings?->getSmtpHost(),
            'port' => $settings?->getSmtpPort(),
            'username' => $settings?->getSmtpUser(),
            'password' => $settings?->getSmtpPassword(),
        ];
    }

    public function getRequirementsMessage(): ?string
    {
        return "You need to configure the email server settings in order to use email notification channel.\nRequired settings: host, port";
    }
}
