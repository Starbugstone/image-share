<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Catch-all route for Vue.js SPA
     * This will serve the Vue.js app for all non-API routes
     */
    #[Route('/{path}', name: 'app_spa', requirements: ['path' => '^(?!api/).*'], priority: -1)]
    public function spa(): Response
    {
        // In development, redirect to Vite dev server
        if ($this->getParameter('kernel.environment') === 'dev') {
            return $this->redirect('http://localhost:5173');
        }
        
        // In production, serve the built Vue.js app
        $indexPath = $this->getParameter('kernel.project_dir') . '/public/dist/index.html';
        if (file_exists($indexPath)) {
            return new Response(file_get_contents($indexPath));
        }
        
        // Fallback for development when dist doesn't exist
        return $this->redirect('http://localhost:5173');
    }
}
