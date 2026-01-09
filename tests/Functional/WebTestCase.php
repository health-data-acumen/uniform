<?php

namespace App\Tests\Functional;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Entity\Settings\AccountSettings;
use App\Entity\Settings\NotificationSettings;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Uid\Uuid;

abstract class WebTestCase extends BaseWebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

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
        string $password = 'password123',
        array $roles = []
    ): User {
        $hasher = self::getContainer()->get('security.user_password_hasher');

        $user = new User();
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setRoles($roles);
        $user->setPassword($hasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function loginUser(User $user): void
    {
        $this->client->loginUser($user);
    }

    protected function createAndLoginUser(
        string $email = 'test@example.com',
        string $fullName = 'Test User',
        string $password = 'password123',
        array $roles = []
    ): User {
        $user = $this->createUser($email, $fullName, $password, $roles);
        $this->loginUser($user);

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

    protected function assertFlashMessage(string $type, string $expectedMessage): void
    {
        $session = $this->client->getRequest()->getSession();
        $flashBag = $session->getFlashBag();
        $messages = $flashBag->get($type);

        $this->assertContains($expectedMessage, $messages, sprintf(
            'Flash message "%s" not found in %s messages. Found: %s',
            $expectedMessage,
            $type,
            implode(', ', $messages)
        ));
    }

    protected function submitForm(string $buttonText, array $formData): void
    {
        $this->client->submitForm($buttonText, $formData);
    }

    protected function getCsrfToken(string $tokenId): string
    {
        // Use the session from the last request
        $session = $this->client->getRequest()?->getSession();
        if (!$session) {
            throw new \RuntimeException('You must make a request before getting a CSRF token to establish a session');
        }

        // Create a token manually using the session storage
        $tokenGenerator = self::getContainer()->get('security.csrf.token_generator');
        $token = $tokenGenerator->generateToken();

        // Store the token in the session
        $session->set('_csrf/' . $tokenId, $token);

        return $token;
    }
}
