<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_index')]
    public function index(): RedirectResponse
    {
        // TODO: Implement a proper dashboard
        return $this->redirectToRoute('app_dashboard_form_endpoint_list');
    }
}
