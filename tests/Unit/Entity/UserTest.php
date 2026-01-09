<?php

namespace App\Tests\Unit\Entity;

use App\Entity\FormDefinition;
use App\Entity\Settings\AccountSettings;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetIdReturnsNullInitially(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $result = $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame($user, $result);
    }

    public function testSetAndGetFullName(): void
    {
        $user = new User();
        $result = $user->setFullName('John Doe');

        $this->assertSame('John Doe', $user->getFullName());
        $this->assertSame($user, $result);
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('identifier@example.com');

        $this->assertSame('identifier@example.com', $user->getUserIdentifier());
    }

    public function testGetUserIdentifierReturnsEmptyStringWhenEmailIsNull(): void
    {
        $user = new User();

        $this->assertSame('', $user->getUserIdentifier());
    }

    public function testGetRolesIncludesRoleUser(): void
    {
        $user = new User();

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetRolesReturnsUniqueRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_USER']);

        $roles = $user->getRoles();

        $this->assertCount(2, $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testGetRolesAlwaysIncludesRoleUserEvenIfNotSet(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testSetRoles(): void
    {
        $user = new User();
        $result = $user->setRoles(['ROLE_ADMIN', 'ROLE_MODERATOR']);

        $this->assertSame($user, $result);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_MODERATOR', $user->getRoles());
    }

    public function testSetAndGetPassword(): void
    {
        $user = new User();
        $result = $user->setPassword('hashed_password_123');

        $this->assertSame('hashed_password_123', $user->getPassword());
        $this->assertSame($user, $result);
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $user = new User();

        $user->eraseCredentials();

        $this->assertTrue(true);
    }

    public function testGetFormEndpointsReturnsEmptyCollectionInitially(): void
    {
        $user = new User();

        $endpoints = $user->getFormEndpoints();

        $this->assertInstanceOf(Collection::class, $endpoints);
        $this->assertCount(0, $endpoints);
    }

    public function testAddFormEndpoint(): void
    {
        $user = new User();
        $formEndpoint = new FormDefinition();

        $result = $user->addFormEndpoint($formEndpoint);

        $this->assertCount(1, $user->getFormEndpoints());
        $this->assertTrue($user->getFormEndpoints()->contains($formEndpoint));
        $this->assertSame($user, $result);
    }

    public function testAddFormEndpointSetsOwner(): void
    {
        $user = new User();
        $formEndpoint = new FormDefinition();

        $user->addFormEndpoint($formEndpoint);

        $this->assertSame($user, $formEndpoint->getOwner());
    }

    public function testAddFormEndpointPreventsDuplicates(): void
    {
        $user = new User();
        $formEndpoint = new FormDefinition();

        $user->addFormEndpoint($formEndpoint);
        $user->addFormEndpoint($formEndpoint);

        $this->assertCount(1, $user->getFormEndpoints());
    }

    public function testRemoveFormEndpoint(): void
    {
        $user = new User();
        $formEndpoint = new FormDefinition();
        $user->addFormEndpoint($formEndpoint);

        $result = $user->removeFormEndpoint($formEndpoint);

        $this->assertCount(0, $user->getFormEndpoints());
        $this->assertSame($user, $result);
    }

    public function testRemoveFormEndpointNullifiesOwner(): void
    {
        $user = new User();
        $formEndpoint = new FormDefinition();
        $user->addFormEndpoint($formEndpoint);

        $user->removeFormEndpoint($formEndpoint);

        $this->assertNull($formEndpoint->getOwner());
    }

    public function testSetAndGetAccountSettings(): void
    {
        $user = new User();
        $accountSettings = new AccountSettings();

        $result = $user->setAccountSettings($accountSettings);

        $this->assertSame($accountSettings, $user->getAccountSettings());
        $this->assertSame($user, $result);
    }

    public function testSetAccountSettingsSetsOwnerOnSettings(): void
    {
        $user = new User();
        $accountSettings = new AccountSettings();

        $user->setAccountSettings($accountSettings);

        $this->assertSame($user, $accountSettings->getOwner());
    }

    public function testAccountSettingsIsNullInitially(): void
    {
        $user = new User();

        $this->assertNull($user->getAccountSettings());
    }

    public function testFluentSetters(): void
    {
        $user = new User();

        $result = $user
            ->setEmail('test@test.com')
            ->setFullName('Test User')
            ->setPassword('password123')
            ->setRoles(['ROLE_ADMIN']);

        $this->assertSame($user, $result);
        $this->assertSame('test@test.com', $user->getEmail());
        $this->assertSame('Test User', $user->getFullName());
        $this->assertSame('password123', $user->getPassword());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testImplementsUserInterface(): void
    {
        $user = new User();

        $this->assertInstanceOf(\Symfony\Component\Security\Core\User\UserInterface::class, $user);
    }

    public function testImplementsPasswordAuthenticatedUserInterface(): void
    {
        $user = new User();

        $this->assertInstanceOf(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface::class, $user);
    }
}
