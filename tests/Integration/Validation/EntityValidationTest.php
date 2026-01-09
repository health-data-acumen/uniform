<?php

namespace App\Tests\Integration\Validation;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Entity\Settings\AccountSettings;
use App\Entity\Settings\NotificationSettings;
use App\Entity\User;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidationTest extends DatabaseTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    // ==================== User Entity Tests ====================
    // Note: User entity has no validation constraints defined
    // These tests document the current behavior

    public function testUserWithValidDataPasses(): void
    {
        $user = new User();
        $user->setEmail('valid@example.com');
        $user->setFullName('Valid User');
        $user->setPassword('hashedpassword');

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors);
    }

    public function testUserEntityAcceptsAnyEmailFormat(): void
    {
        // Note: User entity has no Email constraint - validation happens in forms
        $user = new User();
        $user->setEmail('not-an-email');
        $user->setFullName('Test User');
        $user->setPassword('password');

        $errors = $this->validator->validate($user);

        // No validation constraints on User entity for email format
        $this->assertCount(0, $errors);
    }

    public function testUserEntityAcceptsEmptyFullName(): void
    {
        // Note: User entity has no NotBlank constraint on fullName
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFullName('');
        $user->setPassword('password');

        $errors = $this->validator->validate($user);

        // No validation constraints on User entity for fullName
        $this->assertCount(0, $errors);
    }

    // ==================== FormDefinition Entity Tests ====================

    public function testFormDefinitionWithValidDataPasses(): void
    {
        $user = $this->createUser();

        $form = new FormDefinition();
        $form->setName('Valid Form');
        $form->setOwner($user);
        $form->setEnabled(true);
        $form->setUid(Uuid::v7());

        $errors = $this->validator->validate($form);

        $this->assertCount(0, $errors);
    }

    public function testFormDefinitionWithEmptyNameFails(): void
    {
        $user = $this->createUser();

        $form = new FormDefinition();
        $form->setName('');
        $form->setOwner($user);
        $form->setEnabled(true);

        $errors = $this->validator->validate($form);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testFormDefinitionWithValidRedirectUrlPasses(): void
    {
        $user = $this->createUser();

        $form = new FormDefinition();
        $form->setName('Test Form');
        $form->setOwner($user);
        $form->setEnabled(true);
        $form->setRedirectUrl('https://example.com/thank-you');
        $form->setUid(Uuid::v7());

        $errors = $this->validator->validate($form);

        $this->assertCount(0, $errors);
    }

    public function testFormDefinitionWithNullRedirectUrlPasses(): void
    {
        $user = $this->createUser();

        $form = new FormDefinition();
        $form->setName('Test Form');
        $form->setOwner($user);
        $form->setEnabled(true);
        $form->setRedirectUrl(null);
        $form->setUid(Uuid::v7());

        $errors = $this->validator->validate($form);

        $this->assertCount(0, $errors);
    }

    // ==================== AccountSettings Entity Tests ====================

    public function testAccountSettingsWithAllNullFieldsPasses(): void
    {
        $user = $this->createUser();

        $settings = new AccountSettings();
        $settings->setOwner($user);

        $errors = $this->validator->validate($settings);

        $this->assertCount(0, $errors);
    }

    public function testAccountSettingsWithValidSmtpHostPasses(): void
    {
        $user = $this->createUser();

        $settings = new AccountSettings();
        $settings->setOwner($user);
        $settings->setSmtpHost('smtp.example.com');
        $settings->setSmtpPort(587);

        $errors = $this->validator->validate($settings);

        $this->assertCount(0, $errors);
    }

    public function testAccountSettingsWithNegativePortFails(): void
    {
        $user = $this->createUser();

        $settings = new AccountSettings();
        $settings->setOwner($user);
        $settings->setSmtpPort(-1);

        $errors = $this->validator->validate($settings);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testAccountSettingsWithZeroPortFails(): void
    {
        $user = $this->createUser();

        $settings = new AccountSettings();
        $settings->setOwner($user);
        $settings->setSmtpPort(0);

        $errors = $this->validator->validate($settings);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testAccountSettingsWithInvalidEmailFails(): void
    {
        $user = $this->createUser();

        $settings = new AccountSettings();
        $settings->setOwner($user);
        $settings->setEmailFromAddress('not-an-email');

        $errors = $this->validator->validate($settings);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testAccountSettingsWithValidEmailPasses(): void
    {
        $user = $this->createUser();

        $settings = new AccountSettings();
        $settings->setOwner($user);
        $settings->setEmailFromAddress('valid@example.com');

        $errors = $this->validator->validate($settings);

        $this->assertCount(0, $errors);
    }

    // ==================== NotificationSettings Entity Tests ====================

    public function testNotificationSettingsWithValidDataPasses(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $settings = new NotificationSettings();
        $settings->setForm($form);
        $settings->setType('email');
        $settings->setTarget('recipient@example.com');
        $settings->setEnabled(true);

        $errors = $this->validator->validate($settings);

        $this->assertCount(0, $errors);
    }

    public function testNotificationSettingsWithEmptyTypeFails(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $settings = new NotificationSettings();
        $settings->setForm($form);
        $settings->setType('');
        $settings->setEnabled(true);

        $errors = $this->validator->validate($settings);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testNotificationSettingsWithNullTargetPasses(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $settings = new NotificationSettings();
        $settings->setForm($form);
        $settings->setType('webhook');
        $settings->setTarget(null);
        $settings->setEnabled(true);

        $errors = $this->validator->validate($settings);

        $this->assertCount(0, $errors);
    }

    // ==================== FormSubmission Entity Tests ====================

    public function testFormSubmissionWithValidDataPasses(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload(['email' => 'test@example.com', 'message' => 'Hello']);
        $submission->setSubmittedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($submission);

        $this->assertCount(0, $errors);
    }

    public function testFormSubmissionWithEmptyPayloadPasses(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload([]);
        $submission->setSubmittedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($submission);

        $this->assertCount(0, $errors);
    }

    public function testFormSubmissionWithNestedPayloadPasses(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload([
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'products' => [
                ['id' => 1, 'qty' => 2],
                ['id' => 2, 'qty' => 1],
            ],
        ]);
        $submission->setSubmittedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($submission);

        $this->assertCount(0, $errors);
    }

    // ==================== Edge Cases ====================

    public function testUnicodeCharactersInUserFullName(): void
    {
        $user = new User();
        $user->setEmail('unicode@example.com');
        $user->setFullName('日本語名前 François Müller');
        $user->setPassword('password');

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors);
    }

    public function testUnicodeCharactersInFormName(): void
    {
        $user = $this->createUser();

        $form = new FormDefinition();
        $form->setName('Formulaire français 日本語');
        $form->setOwner($user);
        $form->setEnabled(true);
        $form->setUid(Uuid::v7());

        $errors = $this->validator->validate($form);

        $this->assertCount(0, $errors);
    }

    public function testSpecialCharactersInFormDescription(): void
    {
        $user = $this->createUser();

        $form = new FormDefinition();
        $form->setName('Test Form');
        $form->setDescription('Description with <script>alert("xss")</script> & special "chars"');
        $form->setOwner($user);
        $form->setEnabled(true);
        $form->setUid(Uuid::v7());

        $errors = $this->validator->validate($form);

        $this->assertCount(0, $errors);
    }

    public function testEmailWithPlusAddressingIsValid(): void
    {
        $user = new User();
        $user->setEmail('user+tag@example.com');
        $user->setFullName('Test User');
        $user->setPassword('password');

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors);
    }

    public function testFormSubmissionWithLargePayload(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $largeArray = [];
        for ($i = 0; $i < 100; $i++) {
            $largeArray["field_$i"] = str_repeat('x', 1000);
        }

        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload($largeArray);
        $submission->setSubmittedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($submission);

        $this->assertCount(0, $errors);
    }
}
