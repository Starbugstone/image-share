<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Dashboard routes now redirect to Vue.js frontend
 * All dashboard functionality is handled by API endpoints in ApiController
 */
#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_dashboard')]
    public function index(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/images', name: 'app_dashboard_images')]
    public function images(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/albums', name: 'app_dashboard_albums')]
    public function albums(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/shares', name: 'app_dashboard_shares')]
    public function shares(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/comments', name: 'app_dashboard_comments')]
    public function comments(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }
}
