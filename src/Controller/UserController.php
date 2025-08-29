<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * Search users for sharing
     */
    #[Route('/search', name: 'app_user_search', methods: ['GET'])]
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $currentUserId = $this->getUser()->getId();
        
        if (strlen($query) < 2) {
            return $this->json(['users' => []]);
        }

        $users = $this->userRepository->searchUsersForSharing($query, $currentUserId);
        
        $userData = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'isVerified' => $user->isVerified(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d')
            ];
        }, $users);

        return $this->json(['users' => $userData]);
    }

    /**
     * Get all available users for sharing
     */
    #[Route('/available', name: 'app_user_available', methods: ['GET'])]
    public function getAvailableUsers(): JsonResponse
    {
        $currentUserId = $this->getUser()->getId();
        $users = $this->userRepository->getAvailableUsersForSharing($currentUserId);
        
        $userData = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'isVerified' => $user->isVerified(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d')
            ];
        }, $users);

        return $this->json(['users' => $userData]);
    }

    /**
     * Get user profile by username
     */
    #[Route('/profile/{username}', name: 'app_user_profile')]
    public function getUserProfile(string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        if (!$user->isVerified()) {
            throw $this->createAccessDeniedException('User profile is not available');
        }

        // Redirect to the new profile system
        return $this->redirectToRoute('app_profile_view', ['username' => $username]);
    }
}
