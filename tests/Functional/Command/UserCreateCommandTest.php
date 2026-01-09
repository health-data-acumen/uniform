<?php

namespace App\Tests\Functional\Command;

use App\Entity\User;
use App\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UserCreateCommandTest extends WebTestCase
{
    private Application $application;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application(self::$kernel);
        $command = $this->application->find('app:user:create');
        $this->commandTester = new CommandTester($command);
    }

    public function testCreateUserPersistsToDatabase(): void
    {
        $this->commandTester->execute([
            '--email' => 'newuser@example.com',
            '--full-name' => 'New User',
            '--password' => 'password123',
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $this->clearEntityManager();

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'newuser@example.com']);

        $this->assertNotNull($user);
        $this->assertSame('newuser@example.com', $user->getEmail());
        $this->assertSame('New User', $user->getFullName());
    }

    public function testCreateUserWithAdminRole(): void
    {
        $this->commandTester->execute([
            '--email' => 'admin@example.com',
            '--full-name' => 'Admin User',
            '--password' => 'password123',
            '--admin' => true,
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $this->clearEntityManager();

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        $this->assertNotNull($user);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testCreateUserHashesPasswordCorrectly(): void
    {
        $plainPassword = 'mySecurePassword123';

        $this->commandTester->execute([
            '--email' => 'hashtest@example.com',
            '--full-name' => 'Hash Test',
            '--password' => $plainPassword,
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $this->clearEntityManager();

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'hashtest@example.com']);

        $this->assertNotNull($user);
        $this->assertNotSame($plainPassword, $user->getPassword());
        $this->assertStringStartsWith('$', $user->getPassword());

        $passwordHasher = self::getContainer()->get('security.user_password_hasher');
        $this->assertTrue($passwordHasher->isPasswordValid($user, $plainPassword));
    }

    public function testCreateUserFailsWithDuplicateEmail(): void
    {
        $this->createUser('existing@example.com');

        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);

        $this->commandTester->execute([
            '--email' => 'existing@example.com',
            '--full-name' => 'Duplicate User',
            '--password' => 'password123',
        ]);
    }

    public function testCreateUserWithDefaultRole(): void
    {
        $this->commandTester->execute([
            '--email' => 'defaultrole@example.com',
            '--full-name' => 'Default Role User',
            '--password' => 'password123',
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $this->clearEntityManager();

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'defaultrole@example.com']);

        $this->assertNotNull($user);
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testCreateUserOutputsSuccessMessage(): void
    {
        $this->commandTester->execute([
            '--email' => 'success@example.com',
            '--full-name' => 'Success User',
            '--password' => 'password123',
        ]);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('User created successfully', $this->commandTester->getDisplay());
    }
}
