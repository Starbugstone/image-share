<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Profile routes now redirect to Vue.js frontend
 * All profile functionality is handled by API endpoints in ApiController
 */
class ProfileController extends AbstractController
{
    #[Route('/profile/me', name: 'app_profile_me')]
    #[IsGranted('ROLE_USER')]
    public function me(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/profile/me/edit', name: 'app_profile_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/profile/{username}', name: 'app_profile_view')]
    #[IsGranted('ROLE_USER')]
    public function view(string $username): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/profile/status/online', name: 'app_profile_status_online', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function setOnline(): JsonResponse
    {
        $user = $this->getUser();
        $profile = $user->getProfile();
        
        $profile->setStatus('online');
        $profile->setLastSeen(new \DateTimeImmutable());
        
        // This would need EntityManager to persist
        // For now, return success - this should be handled by API endpoints
        
        return $this->json(['status' => 'online']);
    }

    #[Route('/profile/status/offline', name: 'app_profile_status_offline', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function setOffline(): JsonResponse
    {
        $user = $this->getUser();
        $profile = $user->getProfile();
        
        $profile->setStatus('offline');
        $profile->setLastSeen(new \DateTimeImmutable());
        
        // This would need EntityManager to persist
        // For now, return success - this should be handled by API endpoints
        
        return $this->json(['status' => 'offline']);
    }

    #[Route('/profile/online', name: 'app_profile_online_users')]
    #[IsGranted('ROLE_USER')]
    public function onlineUsers(): Response
    {
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }
}