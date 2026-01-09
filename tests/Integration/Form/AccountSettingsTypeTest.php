<?php

namespace App\Tests\Integration\Form;

use App\Entity\Settings\AccountSettings;
use App\Form\Settings\AccountSettingsType;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class AccountSettingsTypeTest extends DatabaseTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    public function testFormHasAllExpectedFields(): void
    {
        $form = $this->formFactory->create(AccountSettingsType::class, null, [
            'csrf_protection' => false,
        ]);

        $this->assertTrue($form->has('smtpHost'));
        $this->assertTrue($form->has('smtpPort'));
        $this->assertTrue($form->has('smtpUser'));
        $this->assertTrue($form->has('smtpPassword'));
        $this->assertTrue($form->has('emailFromName'));
        $this->assertTrue($form->has('emailFromAddress'));
        $this->assertTrue($form->has('mailerEncryption'));
    }

    public function testFormIsSynchronized(): void
    {
        $user = $this->createUser();
        $accountSettings = new AccountSettings();
        $accountSettings->setOwner($user);

        $formData = [
            'smtpHost' => 'localhost',
            'smtpPort' => 465,
            'smtpUser' => 'testuser',
            'smtpPassword' => 'testpass',
            'emailFromName' => 'Test Sender',
            'emailFromAddress' => 'test@example.com',
            'mailerEncryption' => 'ssl',
        ];

        $form = $this->formFactory->create(AccountSettingsType::class, $accountSettings, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
    }

    public function testBasicFieldMapping(): void
    {
        $user = $this->createUser();
        $accountSettings = new AccountSettings();
        $accountSettings->setOwner($user);

        // Test text fields that don't have complex validation
        $formData = [
            'smtpHost' => null,
            'smtpPort' => null,
            'smtpUser' => 'testuser',
            'smtpPassword' => 'testpass',
            'emailFromName' => 'Test Sender',
            'emailFromAddress' => null,
            'mailerEncryption' => null,
        ];

        $form = $this->formFactory->create(AccountSettingsType::class, $accountSettings, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
        $this->assertSame('testuser', $accountSettings->getSmtpUser());
        $this->assertSame('testpass', $accountSettings->getSmtpPassword());
        $this->assertSame('Test Sender', $accountSettings->getEmailFromName());
    }

    public function testInvalidEmailAddressIsRejected(): void
    {
        $user = $this->createUser();
        $accountSettings = new AccountSettings();
        $accountSettings->setOwner($user);

        $formData = [
            'smtpHost' => null,
            'smtpPort' => null,
            'emailFromAddress' => 'not-an-email',
            'mailerEncryption' => null,
        ];

        $form = $this->formFactory->create(AccountSettingsType::class, $accountSettings, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
    }

    public function testNegativePortIsRejected(): void
    {
        $user = $this->createUser();
        $accountSettings = new AccountSettings();
        $accountSettings->setOwner($user);

        $formData = [
            'smtpHost' => null,
            'smtpPort' => -1,
            'mailerEncryption' => null,
        ];

        $form = $this->formFactory->create(AccountSettingsType::class, $accountSettings, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
    }

    public function testEncryptionOptionsAreValid(): void
    {
        $form = $this->formFactory->create(AccountSettingsType::class, null, [
            'csrf_protection' => false,
        ]);
        $encryptionField = $form->get('mailerEncryption');

        $config = $encryptionField->getConfig();
        $choices = $config->getOption('choices');

        $this->assertArrayHasKey('None', $choices);
        $this->assertArrayHasKey('SSL', $choices);
        $this->assertArrayHasKey('TLS', $choices);
        $this->assertNull($choices['None']);
        $this->assertSame('ssl', $choices['SSL']);
        $this->assertSame('tls', $choices['TLS']);
    }

    public function testAllFieldsNullIsValid(): void
    {
        $user = $this->createUser();
        $accountSettings = new AccountSettings();
        $accountSettings->setOwner($user);

        // All optional fields as null
        $formData = [
            'smtpHost' => null,
            'smtpPort' => null,
            'smtpUser' => null,
            'smtpPassword' => null,
            'emailFromName' => null,
            'emailFromAddress' => null,
            'mailerEncryption' => null,
        ];

        $form = $this->formFactory->create(AccountSettingsType::class, $accountSettings, [
            'csrf_protection' => false,
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
    }
}
