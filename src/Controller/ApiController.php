<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api')]
class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }





    /**
     * Authenticate user by API token
     */
    private function authenticateUser(Request $request): ?User
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
        
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['apiToken' => $token]);
            
        return $user;
    }

    #[Route('/login', name: 'api_login', methods: ['POST', 'OPTIONS'])]
    public function login(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'error' => 'Email and password are required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $email = $data['email'];
            $password = $data['password'];

            // Find user by email
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            
            if (!$user) {
                throw new UserNotFoundException('User not found');
            }

            // Check if user is verified
            if (!$user->isVerified()) {
                throw new CustomUserMessageAuthenticationException('Please verify your email before logging in.');
            }

            // Verify password
            if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                throw new AuthenticationException('Invalid credentials');
            }

            // Generate a simple token (in production, use JWT)
            $token = bin2hex(random_bytes(32));
            
            // Store token in user entity or session (simplified for now)
            $user->setApiToken($token);
            $this->entityManager->flush();

            // Return user data and token
            return $this->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'isVerified' => $user->isVerified(),
                    'roles' => $user->getRoles()
                ]
            ]);

        } catch (UserNotFoundException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (CustomUserMessageAuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred during login'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/register', name: 'api_register', methods: ['POST', 'OPTIONS'])]
    public function register(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['email']) || !isset($data['password']) || !isset($data['username'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Email, password, and username are required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $email = $data['email'];
            $password = $data['password'];
            $username = $data['username'];

            // Check if user already exists
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                return $this->json([
                    'success' => false,
                    'error' => 'User with this email already exists'
                ], Response::HTTP_CONFLICT);
            }

            // Check if username already exists
            $existingUsername = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existingUsername) {
                return $this->json([
                    'success' => false,
                    'error' => 'Username already taken'
                ], Response::HTTP_CONFLICT);
            }

            // Create new user
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(false); // Email verification required

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email for verification.',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'isVerified' => $user->isVerified()
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred during registration'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST', 'OPTIONS'])]
    public function logout(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $user = $this->authenticateUser($request);
            if ($user) {
                $user->setApiToken(null); // Invalidate the token
                $this->entityManager->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred during logout'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/dashboard/stats', name: 'api_dashboard_stats', methods: ['GET', 'OPTIONS'])]
    public function dashboardStats(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $user = $this->authenticateUser($request);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Get user's statistics
            $imageRepository = $this->entityManager->getRepository(\App\Entity\Image::class);
            $albumRepository = $this->entityManager->getRepository(\App\Entity\Album::class);
            $shareRepository = $this->entityManager->getRepository(\App\Entity\Share::class);
            $commentRepository = $this->entityManager->getRepository(\App\Entity\Comment::class);

            $stats = [
                'total_images' => $imageRepository->count(['user' => $user]),
                'total_albums' => $albumRepository->count(['user' => $user]),
                'shared_items' => $shareRepository->count(['sharedBy' => $user]),
                'received_shares' => $shareRepository->count(['sharedWith' => $user]),
                'recent_comments' => count($commentRepository->findVisibleToUser($user, 5)),
            ];

            return $this->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to load dashboard statistics'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/available', name: 'api_users_available', methods: ['GET', 'OPTIONS'])]
    public function availableUsers(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $user = $this->authenticateUser($request);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Get all verified users except the current user
            $users = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.id != :currentUserId')
                ->andWhere('u.isVerified = :verified')
                ->setParameter('currentUserId', $user->getId())
                ->setParameter('verified', true)
                ->orderBy('u.username', 'ASC')
                ->getQuery()
                ->getResult();

            $userData = array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'isVerified' => $user->isVerified(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d')
                ];
            }, $users);

            return $this->json([
                'success' => true,
                'users' => $userData
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to load available users'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/user/profile', name: 'api_user_profile', methods: ['GET', 'OPTIONS'])]
    public function getUserProfile(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $user = $this->authenticateUser($request);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $this->json([
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'isVerified' => $user->isVerified(),
                    'roles' => $user->getRoles(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to load user profile'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/images', name: 'api_images', methods: ['GET', 'OPTIONS'])]
    public function getUserImages(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $user = $this->authenticateUser($request);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $limit = $request->query->get('limit', 10);
            $page = $request->query->get('page', 1);
            $offset = ($page - 1) * $limit;

            // Get user's images with pagination
            $imageRepository = $this->entityManager->getRepository(\App\Entity\Image::class);
            $images = $imageRepository->createQueryBuilder('i')
                ->where('i.user = :user')
                ->setParameter('user', $user)
                ->orderBy('i.createdAt', 'DESC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();

            $imageData = array_map(function($image) {
                return [
                    'id' => $image->getId(),
                    'title' => $image->getTitle(),
                    'description' => $image->getDescription(),
                    'imageName' => $image->getImageName(),
                    'imageSize' => $image->getImageSize(),
                    'createdAt' => $image->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $image->getUpdatedAt() ? $image->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                    'albumId' => $image->getAlbum() ? $image->getAlbum()->getId() : null,
                    'albumName' => $image->getAlbum() ? $image->getAlbum()->getName() : null
                ];
            }, $images);

            return $this->json([
                'success' => true,
                'images' => $imageData,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total' => count($imageData)
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to load images'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/albums', name: 'api_albums', methods: ['GET', 'OPTIONS'])]
    public function getUserAlbums(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $user = $this->authenticateUser($request);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $limit = $request->query->get('limit', 10);
            $page = $request->query->get('page', 1);
            $offset = ($page - 1) * $limit;

            // Get user's albums with pagination
            $albumRepository = $this->entityManager->getRepository(\App\Entity\Album::class);
            $albums = $albumRepository->createQueryBuilder('a')
                ->where('a.user = :user')
                ->setParameter('user', $user)
                ->orderBy('a.createdAt', 'DESC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();

            $albumData = array_map(function($album) {
                return [
                    'id' => $album->getId(),
                    'name' => $album->getName(),
                    'description' => $album->getDescription(),
                    'isPublic' => $album->getIsPublic(),
                    'createdAt' => $album->getCreatedAt()->format('Y-m-d H:i:s'),
                    'imageCount' => $album->getImages()->count()
                ];
            }, $albums);

            return $this->json([
                'success' => true,
                'albums' => $albumData,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total' => count($albumData)
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to load albums'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/shares', name: 'api_shares', methods: ['GET', 'OPTIONS'])]
    public function getUserShares(Request $request): JsonResponse
    {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse();
        }

        try {
            $user = $this->authenticateUser($request);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $limit = $request->query->get('limit', 10);
            $page = $request->query->get('page', 1);
            $offset = ($page - 1) * $limit;

            // Get user's shares with pagination
            $shareRepository = $this->entityManager->getRepository(\App\Entity\Share::class);
            $shares = $shareRepository->createQueryBuilder('s')
                ->where('s.sharedWith = :user')
                ->setParameter('user', $user)
                ->orderBy('s.sharedAt', 'DESC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();

            $shareData = array_map(function($share) {
                return [
                    'id' => $share->getId(),
                    'message' => $share->getMessage(),
                    'sharedAt' => $share->getSharedAt()->format('Y-m-d H:i:s'),
                    'sharedBy' => [
                        'id' => $share->getSharedBy()->getId(),
                        'username' => $share->getSharedBy()->getUsername(),
                        'email' => $share->getSharedBy()->getEmail()
                    ],
                    'image' => $share->getImage() ? [
                        'id' => $share->getImage()->getId(),
                        'title' => $share->getImage()->getTitle(),
                        'imageName' => $share->getImage()->getImageName()
                    ] : null,
                    'album' => $share->getAlbum() ? [
                        'id' => $share->getAlbum()->getId(),
                        'name' => $share->getAlbum()->getName(),
                        'description' => $share->getAlbum()->getDescription()
                    ] : null
                ];
            }, $shares);

            return $this->json([
                'success' => true,
                'shares' => $shareData,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total' => count($shareData)
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to load shares'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




}
