<?php

namespace App\Tests\Unit\Entity\Settings;

use App\Entity\Settings\AccountSettings;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AccountSettingsTest extends TestCase
{
    public function testGetIdReturnsNullInitially(): void
    {
        $settings = new AccountSettings();

        $this->assertNull($settings->getId());
    }

    public function testSetAndGetSmtpHost(): void
    {
        $settings = new AccountSettings();
        $result = $settings->setSmtpHost('smtp.example.com');

        $this->assertSame('smtp.example.com', $settings->getSmtpHost());
        $this->assertSame($settings, $result);
    }

    public function testSmtpHostCanBeNull(): void
    {
        $settings = new AccountSettings();
        $settings->setSmtpHost(null);

        $this->assertNull($settings->getSmtpHost());
    }

    public function testSetAndGetSmtpPort(): void
    {
        $settings = new AccountSettings();
        $result = $settings->setSmtpPort(587);

        $this->assertSame(587, $settings->getSmtpPort());
        $this->assertSame($settings, $result);
    }

    public function testSmtpPortCanBeNull(): void
    {
        $settings = new AccountSettings();
        $settings->setSmtpPort(null);

        $this->assertNull($settings->getSmtpPort());
    }

    public function testSmtpPortCommonValues(): void
    {
        $settings = new AccountSettings();

        $settings->setSmtpPort(25);
        $this->assertSame(25, $settings->getSmtpPort());

        $settings->setSmtpPort(465);
        $this->assertSame(465, $settings->getSmtpPort());

        $settings->setSmtpPort(587);
        $this->assertSame(587, $settings->getSmtpPort());
    }

    public function testSetAndGetSmtpUser(): void
    {
        $settings = new AccountSettings();
        $result = $settings->setSmtpUser('user@example.com');

        $this->assertSame('user@example.com', $settings->getSmtpUser());
        $this->assertSame($settings, $result);
    }

    public function testSmtpUserCanBeNull(): void
    {
        $settings = new AccountSettings();
        $settings->setSmtpUser(null);

        $this->assertNull($settings->getSmtpUser());
    }

    public function testSetAndGetSmtpPassword(): void
    {
        $settings = new AccountSettings();
        $result = $settings->setSmtpPassword('secret123');

        $this->assertSame('secret123', $settings->getSmtpPassword());
        $this->assertSame($settings, $result);
    }

    public function testSmtpPasswordCanBeNull(): void
    {
        $settings = new AccountSettings();
        $settings->setSmtpPassword(null);

        $this->assertNull($settings->getSmtpPassword());
    }

    public function testSetAndGetEmailFromName(): void
    {
        $settings = new AccountSettings();
        $result = $settings->setEmailFromName('My Application');

        $this->assertSame('My Application', $settings->getEmailFromName());
        $this->assertSame($settings, $result);
    }

    public function testEmailFromNameCanBeNull(): void
    {
        $settings = new AccountSettings();
        $settings->setEmailFromName(null);

        $this->assertNull($settings->getEmailFromName());
    }

    public function testSetAndGetEmailFromAddress(): void
    {
        $settings = new AccountSettings();
        $result = $settings->setEmailFromAddress('noreply@example.com');

        $this->assertSame('noreply@example.com', $settings->getEmailFromAddress());
        $this->assertSame($settings, $result);
    }

    public function testEmailFromAddressCanBeNull(): void
    {
        $settings = new AccountSettings();
        $settings->setEmailFromAddress(null);

        $this->assertNull($settings->getEmailFromAddress());
    }

    public function testSetAndGetMailerEncryption(): void
    {
        $settings = new AccountSettings();
        $result = $settings->setMailerEncryption('tls');

        $this->assertSame('tls', $settings->getMailerEncryption());
        $this->assertSame($settings, $result);
    }

    public function testMailerEncryptionCanBeNull(): void
    {
        $settings = new AccountSettings();
        $settings->setMailerEncryption(null);

        $this->assertNull($settings->getMailerEncryption());
    }

    public function testMailerEncryptionValues(): void
    {
        $settings = new AccountSettings();

        $settings->setMailerEncryption('ssl');
        $this->assertSame('ssl', $settings->getMailerEncryption());

        $settings->setMailerEncryption('tls');
        $this->assertSame('tls', $settings->getMailerEncryption());
    }

    public function testSetAndGetOwner(): void
    {
        $settings = new AccountSettings();
        $user = new User();
        $result = $settings->setOwner($user);

        $this->assertSame($user, $settings->getOwner());
        $this->assertSame($settings, $result);
    }

    public function testOwnerIsNullInitially(): void
    {
        $settings = new AccountSettings();

        $this->assertNull($settings->getOwner());
    }

    public function testFluentSetters(): void
    {
        $settings = new AccountSettings();
        $user = new User();

        $result = $settings
            ->setSmtpHost('smtp.test.com')
            ->setSmtpPort(587)
            ->setSmtpUser('testuser')
            ->setSmtpPassword('testpass')
            ->setEmailFromName('Test App')
            ->setEmailFromAddress('test@test.com')
            ->setMailerEncryption('tls')
            ->setOwner($user);

        $this->assertSame($settings, $result);
        $this->assertSame('smtp.test.com', $settings->getSmtpHost());
        $this->assertSame(587, $settings->getSmtpPort());
        $this->assertSame('testuser', $settings->getSmtpUser());
        $this->assertSame('testpass', $settings->getSmtpPassword());
        $this->assertSame('Test App', $settings->getEmailFromName());
        $this->assertSame('test@test.com', $settings->getEmailFromAddress());
        $this->assertSame('tls', $settings->getMailerEncryption());
        $this->assertSame($user, $settings->getOwner());
    }

    public function testInitialStateHasAllNullValues(): void
    {
        $settings = new AccountSettings();

        $this->assertNull($settings->getId());
        $this->assertNull($settings->getSmtpHost());
        $this->assertNull($settings->getSmtpPort());
        $this->assertNull($settings->getSmtpUser());
        $this->assertNull($settings->getSmtpPassword());
        $this->assertNull($settings->getEmailFromName());
        $this->assertNull($settings->getEmailFromAddress());
        $this->assertNull($settings->getMailerEncryption());
        $this->assertNull($settings->getOwner());
    }
}
