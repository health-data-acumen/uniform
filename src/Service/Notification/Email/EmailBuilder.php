<?php

namespace App\Service\Notification\Email;

use App\Entity\FormSubmission;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class EmailBuilder
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildNotificationEmail(FormSubmission $formSubmission): Email
    {
        $context = [
            'answers' => $formSubmission->getPayload(),
            'formName' => $formSubmission->getForm()->getName(),
        ];

        return (new TemplatedEmail())
            ->subject($this->translator->trans('email.submission.notification_subject', ['form' => $formSubmission->getForm()->getName()]))
            ->htmlTemplate('email/submission_notification.html.twig')
            ->textTemplate('email/submission_notification.md.twig')
            ->context($context)
        ;
    }
}
