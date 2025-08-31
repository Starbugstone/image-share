<?php

namespace App\Controller;

use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Image routes now redirect to Vue.js frontend
 * All image functionality is handled by API endpoints in ApiController
 */
#[Route('/images')]
class ImageController extends AbstractController
{
    #[Route('/upload', name: 'app_image_upload')]
    #[IsGranted('ROLE_USER')]
    public function upload(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}', name: 'app_image_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Image $image): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}/edit', name: 'app_image_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Image $image): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}/delete', name: 'app_image_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Image $image): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }
}