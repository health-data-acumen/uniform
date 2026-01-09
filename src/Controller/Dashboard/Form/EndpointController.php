<?php

namespace App\Controller\Dashboard\Form;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use App\Entity\Settings\NotificationSettings;
use App\Form\FormDefinitionType;
use App\Repository\FormDefinitionRepository;
use App\Repository\FormSubmissionRepository;
use App\Service\FormEndpoint\SubmissionService;
use App\Service\Notification\ChannelInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\ExpressionLanguage\Expression;

use function Symfony\Component\Translation\t;

final class EndpointController extends AbstractController
{
    #[Route('/dashboard/forms', name: 'app_dashboard_form_endpoint_list')]
    public function index(FormDefinitionRepository $repository): Response
    {
        return $this->render('admin/form/endpoint/index.html.twig', [
            'endpoints' => $repository->getEndpoints(),
        ]);
    }

    #[Route('/dashboard/forms/new', name: 'app_dashboard_form_endpoint_new', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ('turbo-modal' !== $request->headers->get('Turbo-Frame')) {
            return $this->redirectToRoute('app_dashboard_form_endpoint_list');
        }

        $endpoint = new FormDefinition();
        $endpointForm = $this->createForm(FormDefinitionType::class, $endpoint, [
            'action' => $this->generateUrl('app_dashboard_form_endpoint_new'),
        ]);
        $endpointForm->handleRequest($request);

        if ($endpointForm->isSubmitted() && $endpointForm->isValid()) {
            $endpoint->setOwner($this->getUser());
            $entityManager->persist($endpoint);
            $entityManager->flush();

            $this->addFlash('success', t('flash.form_endpoint.created'));

            return $this->redirectToRoute('app_dashboard_form_endpoint_list');
        }

        return $this->render('admin/form/endpoint/new.html.twig', [
            'endpointForm' => $endpointForm->createView(),
        ]);
    }

    #[Route('/dashboard/forms/{id}/setup', name: 'app_dashboard_form_endpoint_setup', methods: ['GET', 'POST'])]
    public function setup(FormDefinition $formDefinition): Response
    {
        return $this->render('admin/form/endpoint/setup.html.twig', [
            'endpoint' => $formDefinition,
        ]);
    }

    #[Route('/dashboard/forms/{id}/submissions', name: 'app_dashboard_form_endpoint_submission_list', methods: ['GET', 'POST'])]
    public function submissions(
        Request $request,
        FormDefinition $formDefinition,
        FormSubmissionRepository $submissionRepository,
        SubmissionService $submissionService,
    ): Response {
        $paginator = new Pagerfanta(new QueryAdapter($submissionRepository->buildSelectQuery($formDefinition)));
        $paginator->setMaxPerPage($this->getParameter('app.submission.max_per_page'));
        $paginator->setCurrentPage($request->get('page', 1));

        return $this->render('admin/form/endpoint/submissions.html.twig', [
            'endpoint' => $formDefinition,
            'submissions' => $paginator->getCurrentPageResults(),
            'columns' => $submissionService->getPriorityFormFields($formDefinition),
            'paginator' => $paginator,
        ]);
    }

    #[Route('/dashboard/forms/{id}/settings/general', name: 'app_dashboard_form_endpoint_general_settings', methods: ['GET', 'POST'])]
    public function generalSettings(Request $request, FormDefinition $formDefinition, EntityManagerInterface $entityManager): Response
    {
        $endpointForm = $this->createForm(FormDefinitionType::class, $formDefinition, [
            'action' => $this->generateUrl(
                $request->attributes->get('_route'),
                $request->attributes->get('_route_params'),
            ),
        ]);
        $endpointForm->handleRequest($request);

        if ($endpointForm->isSubmitted() && $endpointForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', t('flash.form_endpoint.updated'));

            return $this->redirectToRoute('app_dashboard_form_endpoint_general_settings', ['id' => $formDefinition->getId()]);
        }

        return $this->render('admin/form/endpoint/settings/general.html.twig', [
            'endpointForm' => $endpointForm,
            'endpoint' => $formDefinition,
        ]);
    }

    #[Route('/dashboard/forms/{id}/settings/notifications', name: 'app_dashboard_form_endpoint_notification_settings', methods: ['GET', 'POST'])]
    public function notificationSettings(
        FormDefinition $formDefinition,
        Request $request,
        EntityManagerInterface $entityManager,
        #[AutowireIterator(tag: 'app.notification.provider', defaultIndexMethod: 'getName', defaultPriorityMethod: 'getPriority')]
        iterable $notificationProviders,
    ): Response {
        $notificationProviders = iterator_to_array($notificationProviders);
        $savedProviders = [];
        $settingsForms = [];

        foreach ($formDefinition->getNotificationSettings() as $notificationSettings) {
            $savedProviders[$notificationSettings->getType()] = $notificationSettings;
        }

        foreach ($notificationProviders as $provider) {
            /* @var $provider ChannelInterface */
            $settings = $savedProviders[$provider->getName()]
                ?? (new NotificationSettings())
                    ->setType($provider->getName())
            ;
            $settingsForms[$provider->getName()] = $this->createForm($provider->getConfigurationForm(), $settings);
        }

        if ($request->isMethod('POST')) {
            foreach ($settingsForms as $form) {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    /** @var NotificationSettings $notification */
                    $notification = $form->getData();
                    if (!$notification->isEnabled() && !$notification->getId()) {
                        // If the notification is not enabled, and it's a new one, we don't need to save it
                        continue;
                    }

                    $notification->setForm($formDefinition);
                    $entityManager->persist($notification);
                    $entityManager->flush();

                    $this->addFlash('success', t('flash.form_endpoint.notification_settings.updated'));

                    return $this->redirectToRoute('app_dashboard_form_endpoint_notification_settings', ['id' => $formDefinition->getId()]);
                }
            }
        }

        return $this->render('admin/form/endpoint/settings/notifications.html.twig', [
            'endpoint' => $formDefinition,
            'settingsForms' => array_map(fn (FormInterface $form) => $form->createView(), $settingsForms),
            'channels' => $notificationProviders,
        ], new Response(status: $request->isMethod('GET') ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    #[Route('/dashboard/forms/{id}/delete', name: 'app_dashboard_form_endpoint_delete', methods: ['POST'])]

    #[IsCsrfTokenValid(id: new Expression('"delete_form_" ~ args["formDefinition"].getId()'))]
    public function delete(FormDefinition $formDefinition, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_OWNER', $formDefinition);

        $entityManager->remove($formDefinition);
        $entityManager->flush();

        $this->addFlash('success', t('flash.form_endpoint.deleted'));

        return $this->redirectToRoute('app_dashboard_form_endpoint_list');
    }

    #[Route('/dashboard/forms/{id}/submissions/bulk-delete', name: 'app_dashboard_form_endpoint_submissions_bulk_delete', methods: ['POST'])]
    public function bulkDelete(
        Request $request,
        FormDefinition $formDefinition,
        FormSubmissionRepository $submissionRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_OWNER', $formDefinition);

        $submissionIds = $request->request->get('submission_ids', '');
        
        if (empty($submissionIds)) {
            $this->addFlash('error', t('flash.submission.bulk_delete.no_selection'));
            return $this->redirectToRoute('app_dashboard_form_endpoint_submission_list', ['id' => $formDefinition->getId()]);
        }

        // Parse comma-separated IDs
        $ids = array_filter(array_map('intval', explode(',', $submissionIds)));
        
        if (empty($ids)) {
            $this->addFlash('error', t('flash.submission.bulk_delete.invalid_ids'));
            return $this->redirectToRoute('app_dashboard_form_endpoint_submission_list', ['id' => $formDefinition->getId()]);
        }

        // Fetch submissions and verify they belong to this form
        $submissions = $submissionRepository->createQueryBuilder('s')
            ->where('s.id IN (:ids)')
            ->andWhere('s.form = :form')
            ->setParameter('ids', $ids)
            ->setParameter('form', $formDefinition)
            ->getQuery()
            ->getResult();

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
