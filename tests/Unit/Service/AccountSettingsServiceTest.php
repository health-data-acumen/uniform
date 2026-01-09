<?php

namespace App\Tests\Unit\Service;

use App\Entity\Settings\AccountSettings;
use App\Repository\Settings\AccountSettingsRepository;
use App\Service\AccountSettingsService;
use PHPUnit\Framework\TestCase;

class AccountSettingsServiceTest extends TestCase
{
    public function testGetSettingsReturnsAccountSettings(): void
    {
        $expectedSettings = new AccountSettings();
        $repository = $this->createMock(AccountSettingsRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with([])
            ->willReturn($expectedSettings);

        $service = new AccountSettingsService($repository);

        $result = $service->getSettings();

        $this->assertSame($expectedSettings, $result);
    }

    public function testGetSettingsReturnsNullWhenNoSettings(): void
    {
        $repository = $this->createMock(AccountSettingsRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with([])
            ->willReturn(null);

        $service = new AccountSettingsService($repository);

        $result = $service->getSettings();

        $this->assertNull($result);
    }

    public function testGetSettingsCachesResult(): void
    {
        $expectedSettings = new AccountSettings();
        $repository = $this->createMock(AccountSettingsRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with([])
            ->willReturn($expectedSettings);

        $service = new AccountSettingsService($repository);

        $result1 = $service->getSettings();
        $result2 = $service->getSettings();

        $this->assertSame($expectedSettings, $result1);
        $this->assertSame($expectedSettings, $result2);
        $this->assertSame($result1, $result2);
    }

    public function testGetSettingsQueriesRepositoryOnlyOnce(): void
    {
        $expectedSettings = new AccountSettings();
        $repository = $this->createMock(AccountSettingsRepository::class);
        $repository->expects($this->exactly(1))
            ->method('findOneBy')
            ->willReturn($expectedSettings);

        $service = new AccountSettingsService($repository);

        $service->getSettings();
        $service->getSettings();
        $service->getSettings();
    }

    public function testGetSettingsDoesNotCacheNullResult(): void
    {
        // Note: The current implementation does not cache null results
        // Each call to getSettings() when the result is null will query the repository
        $repository = $this->createMock(AccountSettingsRepository::class);
        $repository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        $service = new AccountSettingsService($repository);

        $result1 = $service->getSettings();
        $result2 = $service->getSettings();

        $this->assertNull($result1);
        $this->assertNull($result2);
    }
}
