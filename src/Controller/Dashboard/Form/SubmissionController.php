<?php

namespace App\Controller\Dashboard\Form;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Message\Command\SendSubmissionNotification;
use App\Repository\FormSubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function Symfony\Component\Translation\t;

final class SubmissionController extends AbstractController
{
    #[Route('/submission/{id:submission}/notifications/send', name: 'app_submission_send_notification', methods: ['POST'])]
    #[IsCsrfTokenValid(id: new Expression('"submission-" ~ args["submission"].getId() ~ "-notification"'))]
    public function sendNotification(FormSubmission $submission, MessageBusInterface $bus): Response
    {
        $bus->dispatch(new SendSubmissionNotification($submission->getId()));

        $this->addFlash('success', t('flash.submission.notification_queued'));

        return $this->redirectToRoute('app_dashboard_form_endpoint_submission_list', [
            'id' => $submission->getForm()->getId(),
        ]);
    }

    #[Route('/dashboard/forms/{id}/submissions/bulk-delete', name: 'app_dashboard_form_endpoint_submissions_bulk_delete', methods: ['POST'])]
    #[IsGranted('ROLE_OWNER', subject: 'formDefinition')]
    public function bulkDelete(
        Request $request,
        FormDefinition $formDefinition,
        FormSubmissionRepository $submissionRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $ids = $request->request->all('ids') ?? [];
        
        if (empty($ids)) {
            $this->addFlash('error', t('flash.submission.bulk_delete.no_selection'));
            return $this->redirectToRoute('app_dashboard_form_endpoint_submission_list', ['id' => $formDefinition->getId()]);
        }

        // Convert to integers and filter
        $ids = array_filter(array_map('intval', $ids));
        
        if (empty($ids)) {
            $this->addFlash('error', t('flash.submission.bulk_delete.invalid_ids'));
            return $this->redirectToRoute('app_dashboard_form_endpoint_submission_list', ['id' => $formDefinition->getId()]);
        }

        // Fetch submissions using repository method
        $submissions = $submissionRepository->findByIdsAndForm($ids, $formDefinition);

        if (empty($submissions)) {
            $this->addFlash('error', t('flash.submission.bulk_delete.not_found'));
            return $this->redirectToRoute('app_dashboard_form_endpoint_submission_list', ['id' => $formDefinition->getId()]);
        }

        // Delete submissions
        foreach ($submissions as $submission) {
            $entityManager->remove($submission);
        }
        $entityManager->flush();

        $count = count($submissions);
        $this->addFlash('success', t('flash.submission.bulk_delete.success', ['count' => $count]));

        return $this->redirectToRoute('app_dashboard_form_endpoint_submission_list', ['id' => $formDefinition->getId()]);
    }
}
