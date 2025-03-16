<?php

namespace App\Controller\Admin\Form;

use App\Entity\FormDefinition;
use App\Form\FormDefinitionType;
use App\Repository\FormDefinitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\Translation\t;

final class EndpointController extends AbstractController
{
    #[Route('/admin/form/endpoints', name: 'app_admin_form_endpoint_list')]
    public function index(FormDefinitionRepository $repository): Response
    {
        return $this->render('admin/form/endpoint/index.html.twig', [
            'endpoints' => $repository->findAll(),
        ]);
    }

    #[Route('/admin/form/endpoints/new', name: 'app_admin_form_endpoint_new', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $endpoint = new FormDefinition();
        $endpointForm = $this->createForm(FormDefinitionType::class, $endpoint, [
            'action' => $this->generateUrl('app_admin_form_endpoint_new'),
        ]);
        $endpointForm->handleRequest($request);

        if ($endpointForm->isSubmitted() && $endpointForm->isValid()) {
            $entityManager->persist($endpoint);
            $entityManager->flush();

            $this->addFlash('success', t('flash.form_endpoint.created'));

            return $this->redirectToRoute('app_admin_form_endpoint_list');
        }

        return $this->render('admin/form/endpoint/new.html.twig', [
            'endpointForm' => $endpointForm->createView(),
        ]);
    }

    #[Route('/admin/form/endpoints/{id}/submissions', name: 'app_admin_form_endpoint_submission_list', methods: ['GET', 'POST'])]
    public function submissions(FormDefinition $formDefinition): Response
    {
        return $this->render('admin/form/endpoint/submissions.html.twig', [
            'endpoint' => $formDefinition,
            'submissions' => [],
        ]);
    }

    #[Route('/admin/form/endpoints/{id}/settings', name: 'app_admin_form_endpoint_settings', methods: ['GET', 'POST'])]
    public function settings(FormDefinition $formDefinition, Request $request, EntityManagerInterface $entityManager): Response
    {
        $endpointForm = $this->createForm(FormDefinitionType::class, $formDefinition, [
            'action' => $this->generateUrl('app_admin_form_endpoint_settings', ['id' => $formDefinition->getId()]),
        ]);
        $endpointForm->handleRequest($request);

        if ($endpointForm->isSubmitted() && $endpointForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', t('flash.form_endpoint.updated'));

            return $this->redirectToRoute('app_admin_form_endpoint_settings', ['id' => $formDefinition->getId()]);
        }

        return $this->render('admin/form/endpoint/settings.html.twig', [
            'endpointForm' => $endpointForm,
            'endpoint' => $formDefinition,
        ]);
    }
}
