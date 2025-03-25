<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default_index')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    #[Route('/contact', name: 'app_default_contact', env: 'dev')]
    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }

    #[Route('/thank-you', name: 'app_form_submit_success')]
    public function submitSuccess(): Response
    {
        return $this->render('default/form/submit_success.html.twig');
    }
}
