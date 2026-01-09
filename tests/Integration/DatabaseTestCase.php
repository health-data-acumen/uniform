<?php

namespace App\Tests\Integration;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Entity\Settings\AccountSettings;
use App\Entity\Settings\NotificationSettings;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }

    private function createSchema(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function createUser(
        string $email = 'test@example.com',
        string $fullName = 'Test User',
        string $password = '$2y$13$hashed_password',
        array $roles = []
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setPassword($password);
        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function createFormDefinition(
        User $owner,
        string $name = 'Test Form',
        ?string $description = null,
        bool $enabled = true,
        ?string $redirectUrl = null
    ): FormDefinition {
        $form = new FormDefinition();
        $form->setName($name);
        $form->setDescription($description);
        $form->setEnabled($enabled);
        $form->setRedirectUrl($redirectUrl);
        $form->setOwner($owner);
        $form->setUid(Uuid::v7());

        $this->entityManager->persist($form);
        $this->entityManager->flush();

        return $form;
    }

    protected function createFormSubmission(
        FormDefinition $form,
        array $payload = ['email' => 'test@example.com'],
        ?\DateTimeImmutable $submittedAt = null
    ): FormSubmission {
        $submission = new FormSubmission();
        $submission->setForm($form);
        $submission->setPayload($payload);
        $submission->setSubmittedAt($submittedAt ?? new \DateTimeImmutable());

        $this->entityManager->persist($submission);
        $this->entityManager->flush();

        return $submission;
    }

    protected function createAccountSettings(User $owner): AccountSettings
    {
        $settings = new AccountSettings();
        $settings->setOwner($owner);

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        return $settings;
    }

    protected function createNotificationSettings(
        FormDefinition $form,
        string $type = 'email',
        string $target = 'admin@example.com',
        bool $enabled = true
    ): NotificationSettings {
        $settings = new NotificationSettings();
        $settings->setForm($form);
        $settings->setType($type);
        $settings->setTarget($target);
        $settings->setEnabled($enabled);

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        return $settings;
    }

    protected function getRepository(string $entityClass): mixed
    {
        return $this->entityManager->getRepository($entityClass);
    }

    protected function refreshEntity(object $entity): void
    {
        $this->entityManager->refresh($entity);
    }

    protected function clearEntityManager(): void
    {
        $this->entityManager->clear();
    }
}
