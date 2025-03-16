<?php

namespace App\Service;

use App\Entity\Settings\AccountSettings;
use App\Repository\Settings\AccountSettingsRepository;

class AccountSettingsService
{
    private ?AccountSettings $settings = null;

    public function __construct(private readonly AccountSettingsRepository $accountSettingsRepository)
    {
    }

    public function getSettings(): ?AccountSettings
    {
        if (null === $this->settings) {
            $this->settings = $this->accountSettingsRepository->findOneBy([]);
        }

        return $this->settings;
    }
}
