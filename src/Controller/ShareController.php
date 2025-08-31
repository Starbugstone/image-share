<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Share routes now redirect to Vue.js frontend
 * All sharing functionality is handled by API endpoints in ApiController
 */
#[IsGranted('ROLE_USER')]
class ShareController extends AbstractController
{
    #[Route('/share/image/{id}', name: 'app_share_image')]
    public function shareImage(Image $image): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/share/album/{id}', name: 'app_share_album')]
    public function shareAlbum(Album $album): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/share/remove/image/{image}/user/{user}', name: 'app_share_remove_image')]
    public function removeImageShare(Image $image, int $user): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/share/remove/album/{album}/user/{user}', name: 'app_share_remove_album')]
    public function removeAlbumShare(Album $album, int $user): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/share/quick-share', name: 'app_quick_share', methods: ['POST'])]
    public function quickShare(): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }
}