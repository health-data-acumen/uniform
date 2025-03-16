<?php

namespace App\Controller;

use App\Entity\FormDefinition;
use App\Service\FormEndpoint\SubmissionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EndpointController extends AbstractController
{
    #[Route('/e/{uid:endpoint}', name: 'app_form_endpoint_submit', methods: ['POST'])]
    public function index(Request $request, FormDefinition $endpoint, SubmissionService $submissionService): Response
    {
        if (!$endpoint->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $submissionService->saveSubmission($endpoint, $request);

        return $endpoint->getRedirectUrl()
            ? $this->redirect($endpoint->getRedirectUrl())
            : $this->redirectToRoute('app_form_submit_success')
        ;
    }
}
