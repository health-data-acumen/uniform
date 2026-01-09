<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Settings\AccountSettings;
use App\Entity\User;
use App\Repository\Settings\AccountSettingsRepository;
use App\Tests\Integration\DatabaseTestCase;

class AccountSettingsRepositoryTest extends DatabaseTestCase
{
    private AccountSettingsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->entityManager->getRepository(AccountSettings::class);
    }

    public function testRepositoryIsInstanceOfCorrectClass(): void
    {
        $this->assertInstanceOf(AccountSettingsRepository::class, $this->repository);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->find(999);

        $this->assertNull($result);
    }

    public function testFindReturnsSettingsWhenExists(): void
    {
        $user = $this->createUser();
        $settings = $this->createAccountSettings($user);

        $result = $this->repository->find($settings->getId());

        $this->assertNotNull($result);
        $this->assertSame($settings->getId(), $result->getId());
    }

    public function testFindByOwnerReturnsCorrectSettings(): void
    {
        $user = $this->createUser();
        $settings = $this->createAccountSettings($user);

        $result = $this->repository->findOneBy(['owner' => $user]);

        $this->assertNotNull($result);
        $this->assertSame($user->getId(), $result->getOwner()->getId());
    }

    public function testFindByOwnerReturnsNullWhenNoSettings(): void
    {
        $user = $this->createUser();

        $result = $this->repository->findOneBy(['owner' => $user]);

        $this->assertNull($result);
    }

    public function testSettingsArePersisted(): void
    {
        $user = $this->createUser();

        $settings = new AccountSettings();
        $settings->setOwner($user);
        $settings->setSmtpHost('smtp.example.com');
        $settings->setSmtpPort(587);
        $settings->setSmtpUser('testuser');
        $settings->setSmtpPassword('testpass');
        $settings->setEmailFromName('Test Sender');
        $settings->setEmailFromAddress('test@example.com');
        $settings->setMailerEncryption('tls');

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $result = $this->repository->find($settings->getId());

        $this->assertNotNull($result);
        $this->assertSame('smtp.example.com', $result->getSmtpHost());
        $this->assertSame(587, $result->getSmtpPort());
        $this->assertSame('testuser', $result->getSmtpUser());
        $this->assertSame('testpass', $result->getSmtpPassword());
        $this->assertSame('Test Sender', $result->getEmailFromName());
        $this->assertSame('test@example.com', $result->getEmailFromAddress());
        $this->assertSame('tls', $result->getMailerEncryption());
    }

    public function testSettingsCanBeUpdated(): void
    {
        $user = $this->createUser();
        $settings = $this->createAccountSettings($user);

        $settings->setSmtpHost('new-smtp.example.com');
        $settings->setSmtpPort(465);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $result = $this->repository->find($settings->getId());

        $this->assertSame('new-smtp.example.com', $result->getSmtpHost());
        $this->assertSame(465, $result->getSmtpPort());
    }

    public function testSettingsCanBeDeleted(): void
    {
        $user = $this->createUser();
        $settings = $this->createAccountSettings($user);
        $settingsId = $settings->getId();

        $this->entityManager->remove($settings);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $result = $this->repository->find($settingsId);

        $this->assertNull($result);
    }

    public function testFindAllReturnsAllSettings(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $this->createAccountSettings($user1);
        $this->createAccountSettings($user2);

        $results = $this->repository->findAll();

        $this->assertCount(2, $results);
    }

    public function testSettingsRelationWithUser(): void
    {
        $user = $this->createUser('test@example.com', 'Test User');
        $settings = $this->createAccountSettings($user);

        $this->clearEntityManager();

        $result = $this->repository->find($settings->getId());
        $owner = $result->getOwner();

        $this->assertInstanceOf(User::class, $owner);
        $this->assertSame('test@example.com', $owner->getEmail());
        $this->assertSame('Test User', $owner->getFullName());
    }
}
