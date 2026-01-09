<?php

namespace App\Tests\Unit\Entity;

use App\Entity\FormDefinition;
use App\Entity\FormField;
use App\Entity\FormSubmission;
use App\Entity\Settings\NotificationSettings;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class FormDefinitionTest extends TestCase
{
    public function testGetIdReturnsNullWhenNotPersisted(): void
    {
        $formDefinition = new FormDefinition();

        $this->assertNull($formDefinition->getId());
    }

    public function testSetAndGetName(): void
    {
        $formDefinition = new FormDefinition();
        $result = $formDefinition->setName('Contact Form');

        $this->assertSame('Contact Form', $formDefinition->getName());
        $this->assertSame($formDefinition, $result);
    }

    public function testSetAndGetDescription(): void
    {
        $formDefinition = new FormDefinition();
        $result = $formDefinition->setDescription('A simple contact form');

        $this->assertSame('A simple contact form', $formDefinition->getDescription());
        $this->assertSame($formDefinition, $result);
    }

    public function testDescriptionCanBeNull(): void
    {
        $formDefinition = new FormDefinition();
        $formDefinition->setDescription(null);

        $this->assertNull($formDefinition->getDescription());
    }

    public function testSetAndGetUid(): void
    {
        $formDefinition = new FormDefinition();
        $uuid = Uuid::v7();
        $result = $formDefinition->setUid($uuid);

        $this->assertSame($uuid, $formDefinition->getUid());
        $this->assertSame($formDefinition, $result);
    }

    public function testPrePersistGeneratesUuidWhenNull(): void
    {
        $formDefinition = new FormDefinition();
        $this->assertNull($formDefinition->getUid());

        $formDefinition->prePersist();

        $this->assertInstanceOf(Uuid::class, $formDefinition->getUid());
    }

    public function testPrePersistDoesNotOverwriteExistingUuid(): void
    {
        $formDefinition = new FormDefinition();
        $existingUuid = Uuid::v7();
        $formDefinition->setUid($existingUuid);

        $formDefinition->prePersist();

        $this->assertSame($existingUuid, $formDefinition->getUid());
    }

    public function testIsEnabledDefaultsToTrue(): void
    {
        $formDefinition = new FormDefinition();

        $this->assertTrue($formDefinition->isEnabled());
    }

    public function testSetAndIsEnabled(): void
    {
        $formDefinition = new FormDefinition();

        $result = $formDefinition->setEnabled(false);
        $this->assertFalse($formDefinition->isEnabled());
        $this->assertSame($formDefinition, $result);

        $formDefinition->setEnabled(true);
        $this->assertTrue($formDefinition->isEnabled());
    }

    public function testSetAndGetRedirectUrl(): void
    {
        $formDefinition = new FormDefinition();
        $result = $formDefinition->setRedirectUrl('https://example.com/thank-you');

        $this->assertSame('https://example.com/thank-you', $formDefinition->getRedirectUrl());
        $this->assertSame($formDefinition, $result);
    }

    public function testRedirectUrlCanBeNull(): void
    {
        $formDefinition = new FormDefinition();
        $formDefinition->setRedirectUrl(null);

        $this->assertNull($formDefinition->getRedirectUrl());
    }

    public function testGetFieldsReturnsEmptyCollectionInitially(): void
    {
        $formDefinition = new FormDefinition();

        $fields = $formDefinition->getFields();

        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertCount(0, $fields);
    }

    public function testAddFieldAddsFieldToCollection(): void
    {
        $formDefinition = new FormDefinition();
        $field = new FormField();

        $result = $formDefinition->addField($field);

        $this->assertCount(1, $formDefinition->getFields());
        $this->assertTrue($formDefinition->getFields()->contains($field));
        $this->assertSame($formDefinition, $result);
    }

    public function testAddFieldSetsFormOnField(): void
    {
        $formDefinition = new FormDefinition();
        $field = new FormField();

        $formDefinition->addField($field);

        $this->assertSame($formDefinition, $field->getForm());
    }

    public function testAddFieldPreventsDuplicates(): void
    {
        $formDefinition = new FormDefinition();
        $field = new FormField();

        $formDefinition->addField($field);
        $formDefinition->addField($field);

        $this->assertCount(1, $formDefinition->getFields());
    }

    public function testRemoveFieldRemovesFromCollection(): void
    {
        $formDefinition = new FormDefinition();
        $field = new FormField();
        $formDefinition->addField($field);

        $result = $formDefinition->removeField($field);

        $this->assertCount(0, $formDefinition->getFields());
        $this->assertSame($formDefinition, $result);
    }

    public function testRemoveFieldNullifiesFormOnField(): void
    {
        $formDefinition = new FormDefinition();
        $field = new FormField();
        $formDefinition->addField($field);

        $formDefinition->removeField($field);

        $this->assertNull($field->getForm());
    }

    public function testGetSubmissionsReturnsEmptyCollectionInitially(): void
    {
        $formDefinition = new FormDefinition();

        $submissions = $formDefinition->getSubmissions();

        $this->assertInstanceOf(Collection::class, $submissions);
        $this->assertCount(0, $submissions);
    }

    public function testAddSubmissionAddsToCollection(): void
    {
        $formDefinition = new FormDefinition();
        $submission = new FormSubmission();

        $result = $formDefinition->addSubmission($submission);

        $this->assertCount(1, $formDefinition->getSubmissions());
        $this->assertTrue($formDefinition->getSubmissions()->contains($submission));
        $this->assertSame($formDefinition, $result);
    }

    public function testAddSubmissionSetsFormOnSubmission(): void
    {
        $formDefinition = new FormDefinition();
        $submission = new FormSubmission();

        $formDefinition->addSubmission($submission);

        $this->assertSame($formDefinition, $submission->getForm());
    }

    public function testAddSubmissionPreventsDuplicates(): void
    {
        $formDefinition = new FormDefinition();
        $submission = new FormSubmission();

        $formDefinition->addSubmission($submission);
        $formDefinition->addSubmission($submission);

        $this->assertCount(1, $formDefinition->getSubmissions());
    }

    public function testRemoveSubmissionRemovesFromCollection(): void
    {
        $formDefinition = new FormDefinition();
        $submission = new FormSubmission();
        $formDefinition->addSubmission($submission);

        $result = $formDefinition->removeSubmission($submission);

        $this->assertCount(0, $formDefinition->getSubmissions());
        $this->assertSame($formDefinition, $result);
    }

    public function testRemoveSubmissionNullifiesFormOnSubmission(): void
    {
        $formDefinition = new FormDefinition();
        $submission = new FormSubmission();
        $formDefinition->addSubmission($submission);

        $formDefinition->removeSubmission($submission);

        $this->assertNull($submission->getForm());
    }

    public function testGetNotificationSettingsReturnsEmptyCollectionInitially(): void
    {
        $formDefinition = new FormDefinition();

        $settings = $formDefinition->getNotificationSettings();

        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertCount(0, $settings);
    }

    public function testAddNotificationSetting(): void
    {
        $formDefinition = new FormDefinition();
        $notificationSetting = new NotificationSettings();

        $result = $formDefinition->addNotificationSetting($notificationSetting);

        $this->assertCount(1, $formDefinition->getNotificationSettings());
        $this->assertTrue($formDefinition->getNotificationSettings()->contains($notificationSetting));
        $this->assertSame($formDefinition, $notificationSetting->getForm());
        $this->assertSame($formDefinition, $result);
    }

    public function testAddNotificationSettingPreventsDuplicates(): void
    {
        $formDefinition = new FormDefinition();
        $notificationSetting = new NotificationSettings();

        $formDefinition->addNotificationSetting($notificationSetting);
        $formDefinition->addNotificationSetting($notificationSetting);

        $this->assertCount(1, $formDefinition->getNotificationSettings());
    }

    public function testRemoveNotificationSetting(): void
    {
        $formDefinition = new FormDefinition();
        $notificationSetting = new NotificationSettings();
        $formDefinition->addNotificationSetting($notificationSetting);

        $result = $formDefinition->removeNotificationSetting($notificationSetting);

        $this->assertCount(0, $formDefinition->getNotificationSettings());
        $this->assertNull($notificationSetting->getForm());
        $this->assertSame($formDefinition, $result);
    }

    public function testSetAndGetOwner(): void
    {
        $formDefinition = new FormDefinition();
        $user = new User();

        $result = $formDefinition->setOwner($user);

        $this->assertSame($user, $formDefinition->getOwner());
        $this->assertSame($formDefinition, $result);
    }

    public function testOwnerCanBeNull(): void
    {
        $formDefinition = new FormDefinition();
        $formDefinition->setOwner(null);

        $this->assertNull($formDefinition->getOwner());
    }

    public function testFluentSetters(): void
    {
        $formDefinition = new FormDefinition();
        $user = new User();

        $result = $formDefinition
            ->setName('Test Form')
            ->setDescription('Test Description')
            ->setEnabled(true)
            ->setRedirectUrl('https://example.com')
            ->setOwner($user);

        $this->assertSame($formDefinition, $result);
        $this->assertSame('Test Form', $formDefinition->getName());
        $this->assertSame('Test Description', $formDefinition->getDescription());
        $this->assertTrue($formDefinition->isEnabled());
        $this->assertSame('https://example.com', $formDefinition->getRedirectUrl());
        $this->assertSame($user, $formDefinition->getOwner());
    }
}
