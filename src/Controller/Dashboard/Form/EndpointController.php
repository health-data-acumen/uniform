<?php

namespace App\Controller\Dashboard\Form;

use App\Entity\FormDefinition;
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
            // 'submissions' => $submissionRepository->findBy(['form' => $formDefinition]),
            // 'submissions' => $paginator->autoPagingIterator(),
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
}
