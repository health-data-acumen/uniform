<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends DatabaseTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(User::class);
    }

    public function testFindByEmailReturnsUser(): void
    {
        $this->createUser('john@example.com', 'John Doe');

        $found = $this->repository->findOneBy(['email' => 'john@example.com']);

        $this->assertNotNull($found);
        $this->assertSame('john@example.com', $found->getEmail());
        $this->assertSame('John Doe', $found->getFullName());
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findOneBy(['email' => 'nonexistent@example.com']);

        $this->assertNull($found);
    }

    public function testUpgradePasswordUpdatesPassword(): void
    {
        $user = $this->createUser('test@example.com');
        $originalPassword = $user->getPassword();

        $this->repository->upgradePassword($user, 'new_hashed_password');

        $this->assertSame('new_hashed_password', $user->getPassword());
        $this->assertNotSame($originalPassword, $user->getPassword());
    }

    public function testUpgradePasswordFlushesChanges(): void
    {
        $user = $this->createUser('test@example.com');
        $userId = $user->getId();

        $this->repository->upgradePassword($user, 'updated_password');

        $this->clearEntityManager();

        $reloaded = $this->repository->find($userId);
        $this->assertSame('updated_password', $reloaded->getPassword());
    }

    public function testUpgradePasswordThrowsExceptionForWrongUserType(): void
    {
        $wrongUser = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string
            {
                return 'password';
            }
        };

        $this->expectException(UnsupportedUserException::class);
        $this->repository->upgradePassword($wrongUser, 'new_password');
    }

    public function testUserPersistenceWithRoles(): void
    {
        $user = $this->createUser('admin@example.com', 'Admin User', 'password', ['ROLE_ADMIN']);

        $this->clearEntityManager();

        $reloaded = $this->repository->find($user->getId());

        $this->assertContains('ROLE_ADMIN', $reloaded->getRoles());
        $this->assertContains('ROLE_USER', $reloaded->getRoles());
    }

    public function testUserWithFormEndpoints(): void
    {
        $user = $this->createUser();
        $form1 = $this->createFormDefinition($user, 'Form 1');
        $form2 = $this->createFormDefinition($user, 'Form 2');

        $this->clearEntityManager();

        $reloaded = $this->repository->find($user->getId());

        $this->assertCount(2, $reloaded->getFormEndpoints());
    }

    public function testUserWithAccountSettings(): void
    {
        $user = $this->createUser();
        $settings = $this->createAccountSettings($user);
        $settings->setSmtpHost('smtp.example.com');
        $settings->setSmtpPort(587);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $reloaded = $this->repository->find($user->getId());

        $this->assertNotNull($reloaded->getAccountSettings());
        $this->assertSame('smtp.example.com', $reloaded->getAccountSettings()->getSmtpHost());
    }

    public function testOrphanRemovalOnFormEndpoints(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'To Be Removed');
        $formId = $form->getId();

        // Refresh the user to ensure the collection is properly initialized
        $this->clearEntityManager();
        $reloadedUser = $this->repository->find($user->getId());

        // Initialize the collection by accessing it
        $forms = $reloadedUser->getFormEndpoints();
        $formToRemove = null;
        foreach ($forms as $f) {
            if ($f->getId() === $formId) {
                $formToRemove = $f;
                break;
            }
        }

        // Orphan removal happens when we remove from the collection
        $this->assertNotNull($formToRemove);
        $reloadedUser->removeFormEndpoint($formToRemove);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $formRepository = $this->entityManager->getRepository(\App\Entity\FormDefinition::class);
        $deletedForm = $formRepository->find($formId);

        $this->assertNull($deletedForm);
    }
}
