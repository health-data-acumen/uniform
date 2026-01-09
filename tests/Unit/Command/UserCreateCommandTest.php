<?php

namespace App\Tests\Unit\Command;

use App\Command\UserCreateCommand;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCreateCommandTest extends TestCase
{
    public function testExecuteCreatesUser(): void
    {
        $persistedUser = null;

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($user) use (&$persistedUser) {
                $persistedUser = $user;

                return $user instanceof User;
            }));
        $entityManager->expects($this->once())->method('flush');

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'test@example.com',
            '--password' => 'password123',
            '--full-name' => 'Test User',
        ]);

        $this->assertInstanceOf(User::class, $persistedUser);
    }

    public function testExecuteHashesPassword(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with(
                $this->isInstanceOf(User::class),
                'mySecurePassword123'
            )
            ->willReturn('hashed_secure_password');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'test@example.com',
            '--password' => 'mySecurePassword123',
            '--full-name' => 'Test User',
        ]);
    }

    public function testExecutePersistsUser(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hash');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'test@example.com',
            '--password' => 'password123',
            '--full-name' => 'Test User',
        ]);
    }

    public function testExecuteSetsEmailFromOption(): void
    {
        $persistedUser = null;

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hash');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')
            ->willReturnCallback(function ($user) use (&$persistedUser) {
                $persistedUser = $user;
            });

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'custom@email.com',
            '--password' => 'password123',
            '--full-name' => 'Test User',
        ]);

        $this->assertSame('custom@email.com', $persistedUser->getEmail());
    }

    public function testExecuteSetsFullNameFromOption(): void
    {
        $persistedUser = null;

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hash');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')
            ->willReturnCallback(function ($user) use (&$persistedUser) {
                $persistedUser = $user;
            });

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'test@example.com',
            '--password' => 'password123',
            '--full-name' => 'John Doe',
        ]);

        $this->assertSame('John Doe', $persistedUser->getFullName());
    }

    public function testExecuteSetsRoleUserByDefault(): void
    {
        $persistedUser = null;

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hash');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')
            ->willReturnCallback(function ($user) use (&$persistedUser) {
                $persistedUser = $user;
            });

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'test@example.com',
            '--password' => 'password123',
            '--full-name' => 'Test User',
        ]);

        $this->assertContains('ROLE_USER', $persistedUser->getRoles());
        $this->assertNotContains('ROLE_ADMIN', $persistedUser->getRoles());
    }

    public function testExecuteSetsRoleAdminWhenFlagged(): void
    {
        $persistedUser = null;

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hash');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')
            ->willReturnCallback(function ($user) use (&$persistedUser) {
                $persistedUser = $user;
            });

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'admin@example.com',
            '--password' => 'password123',
            '--full-name' => 'Admin User',
            '--admin' => true,
        ]);

        $this->assertContains('ROLE_ADMIN', $persistedUser->getRoles());
    }

    public function testExecuteReturnsSuccessCode(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hash');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $exitCode = $commandTester->execute([
            '--email' => 'test@example.com',
            '--password' => 'password123',
            '--full-name' => 'Test User',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function testExecuteOutputsSuccessMessage(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hash');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $command = new UserCreateCommand($passwordHasher, $entityManager);
        $commandTester = $this->createCommandTester($command);

        $commandTester->execute([
            '--email' => 'test@example.com',
            '--password' => 'password123',
            '--full-name' => 'Test User',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('User created successfully', $output);
    }

    public function testCommandName(): void
    {
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $command = new UserCreateCommand($passwordHasher, $entityManager);

        $this->assertSame('app:user:create', $command->getName());
    }

    private function createCommandTester(Command $command): CommandTester
    {
        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('app:user:create'));
    }
}
