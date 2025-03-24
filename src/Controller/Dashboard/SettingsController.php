<?php

namespace App\Controller\Dashboard;

use App\Entity\Settings\AccountSettings;
use App\Form\Settings\AccountSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettingsController extends AbstractController
{
    #[Route('/dashboard/settings', name: 'app_dashboard_settings', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $settings = $entityManager->getRepository(AccountSettings::class)->findOneBy(['owner' => $this->getUser()]) ?? new AccountSettings();
        $settingsForm = $this->createForm(AccountSettingsType::class, $settings);

        $settingsForm->handleRequest($request);
        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $entityManager->persist($settings);
            $entityManager->flush();

            $this->addFlash('success', 'Settings saved successfully');

            return $this->redirectToRoute('app_dashboard_settings');
        }

        return $this->render('admin/settings/index.html.twig', [
            'settingsForm' => $settingsForm,
        ]);
    }
}
