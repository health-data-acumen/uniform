<?php

namespace App\Tests\Integration\Repository;

use App\Entity\FormDefinition;
use App\Entity\Settings\NotificationSettings;
use App\Repository\Settings\NotificationSettingsRepository;
use App\Tests\Integration\DatabaseTestCase;

class NotificationSettingsRepositoryTest extends DatabaseTestCase
{
    private NotificationSettingsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->entityManager->getRepository(NotificationSettings::class);
    }

    public function testRepositoryIsInstanceOfCorrectClass(): void
    {
        $this->assertInstanceOf(NotificationSettingsRepository::class, $this->repository);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->find(999);

        $this->assertNull($result);
    }

    public function testFindReturnsSettingsWhenExists(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $settings = $this->createNotificationSettings($form);

        $result = $this->repository->find($settings->getId());

        $this->assertNotNull($result);
        $this->assertSame($settings->getId(), $result->getId());
    }

    public function testFindByFormReturnsCorrectSettings(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $this->createNotificationSettings($form, 'email', 'admin@example.com', true);

        $results = $this->repository->findBy(['form' => $form]);

        $this->assertCount(1, $results);
        $this->assertSame($form->getId(), $results[0]->getForm()->getId());
    }

    public function testFindByFormReturnsEmptyWhenNoSettings(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $results = $this->repository->findBy(['form' => $form]);

        $this->assertCount(0, $results);
    }

    public function testFindByTypeReturnsCorrectSettings(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $this->createNotificationSettings($form, 'email', 'admin@example.com');
        $this->createNotificationSettings($form, 'webhook', 'https://example.com/hook');

        $results = $this->repository->findBy(['type' => 'email']);

        $this->assertCount(1, $results);
        $this->assertSame('email', $results[0]->getType());
    }

    public function testFindEnabledSettings(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $this->createNotificationSettings($form, 'email', 'enabled@example.com', true);
        $this->createNotificationSettings($form, 'email', 'disabled@example.com', false);

        $results = $this->repository->findBy(['enabled' => true]);

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->isEnabled());
    }

    public function testSettingsArePersisted(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $settings = new NotificationSettings();
        $settings->setForm($form);
        $settings->setType('email');
        $settings->setTarget('recipient@example.com');
        $settings->setEnabled(true);
        $settings->setOptions(['template' => 'custom']);

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $result = $this->repository->find($settings->getId());

        $this->assertNotNull($result);
        $this->assertSame('email', $result->getType());
        $this->assertSame('recipient@example.com', $result->getTarget());
        $this->assertTrue($result->isEnabled());
        $this->assertSame(['template' => 'custom'], $result->getOptions());
    }

    public function testSettingsCanBeUpdated(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $settings = $this->createNotificationSettings($form, 'email', 'old@example.com', true);

        $settings->setTarget('new@example.com');
        $settings->setEnabled(false);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $result = $this->repository->find($settings->getId());

        $this->assertSame('new@example.com', $result->getTarget());
        $this->assertFalse($result->isEnabled());
    }

    public function testSettingsCanBeDeleted(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $settings = $this->createNotificationSettings($form);
        $settingsId = $settings->getId();

        $this->entityManager->remove($settings);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $result = $this->repository->find($settingsId);

        $this->assertNull($result);
    }

    public function testMultipleSettingsPerForm(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $this->createNotificationSettings($form, 'email', 'admin@example.com');
        $this->createNotificationSettings($form, 'webhook', 'https://example.com/hook');
        $this->createNotificationSettings($form, 'slack', '#notifications');

        $results = $this->repository->findBy(['form' => $form]);

        $this->assertCount(3, $results);
    }

    public function testFindByFormAndType(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);
        $this->createNotificationSettings($form, 'email', 'admin@example.com');
        $this->createNotificationSettings($form, 'webhook', 'https://example.com/hook');

        $result = $this->repository->findOneBy(['form' => $form, 'type' => 'webhook']);

        $this->assertNotNull($result);
        $this->assertSame('webhook', $result->getType());
        $this->assertSame('https://example.com/hook', $result->getTarget());
    }

    public function testSettingsRelationWithForm(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user, 'Test Form');
        $settings = $this->createNotificationSettings($form);

        $this->clearEntityManager();

        $result = $this->repository->find($settings->getId());
        $relatedForm = $result->getForm();

        $this->assertInstanceOf(FormDefinition::class, $relatedForm);
        $this->assertSame('Test Form', $relatedForm->getName());
    }

    public function testOptionsArrayPersistence(): void
    {
        $user = $this->createUser();
        $form = $this->createFormDefinition($user);

        $settings = new NotificationSettings();
        $settings->setForm($form);
        $settings->setType('email');
        $settings->setEnabled(true);
        $settings->setOptions([
            'template' => 'custom',
            'priority' => 'high',
            'recipients' => ['admin@example.com', 'manager@example.com'],
        ]);

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        $this->clearEntityManager();

        $result = $this->repository->find($settings->getId());
        $options = $result->getOptions();

        $this->assertSame('custom', $options['template']);
        $this->assertSame('high', $options['priority']);
        $this->assertCount(2, $options['recipients']);
    }
}
