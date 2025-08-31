<?php

namespace App\Controller;

use App\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Album routes now redirect to Vue.js frontend
 * All album functionality is handled by API endpoints in ApiController
 */
#[Route('/albums')]
#[IsGranted('ROLE_USER')]
class AlbumController extends AbstractController
{
    #[Route('/create', name: 'app_album_create')]
    public function create(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}', name: 'app_album_show')]
    public function show(Album $album): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}/edit', name: 'app_album_edit')]
    public function edit(Album $album): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}/delete', name: 'app_album_delete', methods: ['POST'])]
    public function delete(Album $album): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}/add-images', name: 'app_album_add_images')]
    public function addImages(Album $album): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }
}
