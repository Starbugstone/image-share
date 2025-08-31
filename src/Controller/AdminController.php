<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * Authenticate user from Bearer token and ensure admin role
     */
    private function authenticateAdmin(Request $request): ?User
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        $token = substr($authHeader, 7);
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['apiToken' => $token]);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return null;
        }
        return $user;
    }

    #[Route('/users', name: 'admin_users', methods: ['GET', 'OPTIONS'])]
    public function listUsers(Request $request, UserRepository $userRepository): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }
        $admin = $this->authenticateAdmin($request);
        if (!$admin) {
            return $this->json([
                'success' => false,
                'error' => 'Admin authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $users = $userRepository->findAllWithStats();
        $data = array_map(function($row) {
            /** @var User $u */
            $u = $row[0];
            return [
                'id' => $u->getId(),
                'username' => $u->getUsername(),
                'email' => $u->getEmail(),
                'roles' => $u->getRoles(),
                'stats' => [
                    'total_images' => (int)$row['total_images'],
                    'total_albums' => (int)$row['total_albums'],
                    'shared_images' => (int)$row['shared_images'],
                    'shared_albums' => (int)$row['shared_albums'],
                ]
            ];
        }, $users);
        return $this->json($data);
    }

    #[Route('/users/{id}', name: 'admin_user_detail', methods: ['GET', 'OPTIONS'])]
    public function getUserDetail(int $id, Request $request, UserRepository $userRepository): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }
        $admin = $this->authenticateAdmin($request);
        if (!$admin) {
            return $this->json([
                'success' => false,
                'error' => 'Admin authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
        $images = array_map(function($image) {
            return [
                'id' => $image->getId(),
                'title' => $image->getTitle(),
                'imageName' => $image->getImageName(),
            ];
        }, $user->getImages()->toArray());
        $albums = array_map(function($album) {
            return [
                'id' => $album->getId(),
                'name' => $album->getName(),
            ];
        }, $user->getAlbums()->toArray());
        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
            'images' => $images,
            'albums' => $albums,
        ]);
    }
}
