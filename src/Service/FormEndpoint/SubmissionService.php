<?php

namespace App\Service\FormEndpoint;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class SubmissionService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function saveSubmission(FormDefinition $endpoint, Request $request): FormSubmission
    {
        $payload = $request->getPayload()->all();

        $submission = (new FormSubmission())
            ->setForm($endpoint)
            ->setPayload($payload)
        ;

        $this->entityManager->persist($submission);
        $this->entityManager->flush();

        return $submission;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function getSubmittedFields(FormDefinition $endpoint): array
    {
        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery(
            'SELECT DISTINCT json_each.key FROM form_submissions, json_each(form_submissions.payload) WHERE form_submissions.form_id = :form',
            ['form' => $endpoint->getId()]
        );

        return array_column($result->fetchAllAssociative(), 'key');
    }

    public function getPriorityFormFields(FormDefinition $endpoint, int $max = 2): array
    {
        $keys = $this->getSubmittedFields($endpoint);
        $columns = [];

        foreach (['email', 'name', 'subject', 'message'] as $key) {
            if (in_array($key, $keys, true)) {
                $columns[] = $key;
            }

            if (count($columns) >= $max) {
                break;
            }
        }

        if (empty($columns) && !empty($keys)) {
            $columns[] = $keys[0];
        }

        return $columns;
    }
}
