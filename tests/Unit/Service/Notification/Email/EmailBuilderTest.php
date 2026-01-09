<?php

namespace App\Tests\Unit\Service\Notification\Email;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Service\Notification\Email\EmailBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailBuilderTest extends TestCase
{
    public function testBuildNotificationEmailReturnsTemplatedEmail(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Test Subject');

        $formSubmission = $this->createFormSubmissionMock('Test Form', []);

        $builder = new EmailBuilder($translator);
        $result = $builder->buildNotificationEmail($formSubmission);

        $this->assertInstanceOf(TemplatedEmail::class, $result);
    }

    public function testBuildNotificationEmailSetsCorrectSubject(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('trans')
            ->with('email.submission.notification_subject', ['form' => 'Contact Form'])
            ->willReturn('New submission from Contact Form');

        $formSubmission = $this->createFormSubmissionMock('Contact Form', []);

        $builder = new EmailBuilder($translator);
        $result = $builder->buildNotificationEmail($formSubmission);

        $this->assertSame('New submission from Contact Form', $result->getSubject());
    }

    public function testBuildNotificationEmailSetsHtmlTemplate(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Subject');

        $formSubmission = $this->createFormSubmissionMock('Form', []);

        $builder = new EmailBuilder($translator);
        $result = $builder->buildNotificationEmail($formSubmission);

        $this->assertInstanceOf(TemplatedEmail::class, $result);
        $this->assertSame('email/submission_notification.html.twig', $result->getHtmlTemplate());
    }

    public function testBuildNotificationEmailSetsTextTemplate(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Subject');

        $formSubmission = $this->createFormSubmissionMock('Form', []);

        $builder = new EmailBuilder($translator);
        $result = $builder->buildNotificationEmail($formSubmission);

        $this->assertInstanceOf(TemplatedEmail::class, $result);
        $this->assertSame('email/submission_notification.md.twig', $result->getTextTemplate());
    }

    public function testBuildNotificationEmailSetsCorrectContext(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Subject');

        $payload = ['email' => 'test@example.com', 'message' => 'Hello'];
        $formSubmission = $this->createFormSubmissionMock('My Form', $payload);

        $builder = new EmailBuilder($translator);
        $result = $builder->buildNotificationEmail($formSubmission);

        $context = $result->getContext();
        $this->assertArrayHasKey('answers', $context);
        $this->assertArrayHasKey('formName', $context);
        $this->assertSame('My Form', $context['formName']);
    }

    public function testBuildNotificationEmailIncludesNotificationPayload(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Subject');

        $notificationPayload = [
            'name' => 'John',
            'email' => 'john@example.com',
            'submittedAt' => '2024-01-15 10:30:00',
        ];

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getName')->willReturn('Form');

        $formSubmission = $this->createMock(FormSubmission::class);
        $formSubmission->method('getForm')->willReturn($formDefinition);
        $formSubmission->method('getNotificationPayload')->willReturn($notificationPayload);

        $builder = new EmailBuilder($translator);
        $result = $builder->buildNotificationEmail($formSubmission);

        $context = $result->getContext();
        $this->assertSame($notificationPayload, $context['answers']);
    }

    public function testBuildNotificationEmailWithEmptyPayload(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Subject');

        $formSubmission = $this->createFormSubmissionMock('Form', []);

        $builder = new EmailBuilder($translator);
        $result = $builder->buildNotificationEmail($formSubmission);

        $context = $result->getContext();
        $this->assertIsArray($context['answers']);
    }

    private function createFormSubmissionMock(string $formName, array $notificationPayload): FormSubmission
    {
        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition->method('getName')->willReturn($formName);

        $formSubmission = $this->createMock(FormSubmission::class);
        $formSubmission->method('getForm')->willReturn($formDefinition);
        $formSubmission->method('getNotificationPayload')->willReturn($notificationPayload);

        return $formSubmission;
    }
}
