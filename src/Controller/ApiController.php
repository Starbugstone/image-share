<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\UserProfile;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
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
    private EmailVerifier $emailVerifier;

    public function __construct(
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher,
        EmailVerifier $emailVerifier
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->emailVerifier = $emailVerifier;
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

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {

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

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {

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

            // Send verification email
            try {
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('noreply@imageshare.com', 'ImageShare'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email - ImageShare')
                        ->htmlTemplate('email/verification.html.twig')
                        ->context(['user' => $user])
                );
            } catch (\Exception $e) {
                // Log the error but don't fail registration
                error_log('Failed to send verification email: ' . $e->getMessage());
            }

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

    #[Route('/verify-email', name: 'api_verify_email', methods: ['POST'])]
    public function verifyEmail(Request $request): JsonResponse
    {

        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['id']) || !isset($data['token']) || !isset($data['expires']) || !isset($data['signature'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid verification parameters'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Find user
            $user = $this->entityManager->getRepository(User::class)->find($data['id']);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if already verified
            if ($user->isVerified()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Email address is already verified'
                ], Response::HTTP_CONFLICT);
            }

            // Create a mock request with the verification URL for the EmailVerifier
            $frontendUrl = $this->getParameter('app.frontend_url') ?? $_ENV['FRONTEND_URL'] ?? 'http://localhost:5175';
            $verificationUrl = sprintf(
                '%s/verify/email?id=%s&token=%s&expires=%s&signature=%s',
                rtrim($frontendUrl, '/'),
                $data['id'],
                urlencode($data['token']),
                $data['expires'],
                urlencode($data['signature'])
            );

            $mockRequest = new Request();
            $mockRequest->server->set('REQUEST_URI', $verificationUrl);

            // Use EmailVerifier to handle verification
            $this->emailVerifier->handleEmailConfirmation($mockRequest, $user);

            return $this->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'isVerified' => $user->isVerified()
                ]
            ]);

        } catch (\Exception $e) {
            $errorMessage = 'Email verification failed';
            
            // Provide more specific error messages
            if (str_contains($e->getMessage(), 'expired')) {
                $errorMessage = 'Verification link has expired. Please request a new one.';
            } elseif (str_contains($e->getMessage(), 'invalid')) {
                $errorMessage = 'Invalid verification link. Please check the URL and try again.';
            }

            return $this->json([
                'success' => false,
                'error' => $errorMessage
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/resend-verification', name: 'api_resend_verification', methods: ['POST'])]
    public function resendVerification(Request $request): JsonResponse
    {

        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['userId'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'User ID is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Find user
            $user = $this->entityManager->getRepository(User::class)->find($data['userId']);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if already verified
            if ($user->isVerified()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Email address is already verified'
                ], Response::HTTP_CONFLICT);
            }

            // Send new verification email
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@imageshare.com', 'ImageShare'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email - ImageShare')
                    ->htmlTemplate('email/verification.html.twig')
                    ->context(['user' => $user])
            );

            return $this->json([
                'success' => true,
                'message' => 'Verification email sent successfully'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to send verification email'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {

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

    #[Route('/dashboard/stats', name: 'api_dashboard_stats', methods: ['GET'])]
    public function dashboardStats(Request $request): JsonResponse
    {

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

    #[Route('/users/available', name: 'api_users_available', methods: ['GET'])]
    public function availableUsers(Request $request): JsonResponse
    {

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

    #[Route('/user/profile', name: 'api_user_profile', methods: ['GET'])]
    public function getUserProfile(Request $request): JsonResponse
    {

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

    #[Route('/user/profile', name: 'api_user_profile_update', methods: ['PUT', 'OPTIONS'])]
    public function updateUserProfile(Request $request): JsonResponse
    {
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

            $data = $request->request->all();
            if (empty($data)) {
                $data = json_decode($request->getContent(), true) ?? [];
            }

            $profile = $user->getProfile();
            if (!$profile) {
                $profile = new UserProfile($user);
                $user->setProfile($profile);
                $this->entityManager->persist($profile);
            }

            if (isset($data['displayName'])) {
                $profile->setDisplayName($data['displayName']);
            }
            if (isset($data['bio'])) {
                $profile->setBio($data['bio']);
            }
            if (isset($data['location'])) {
                $profile->setLocation($data['location']);
            }
            if (isset($data['website'])) {
                $profile->setWebsite($data['website']);
            }
            if (isset($data['status'])) {
                $profile->setStatus($data['status']);
            }
            if (array_key_exists('isPublic', $data)) {
                $profile->setIsPublic((bool)$data['isPublic']);
            }

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile */
            $imageFile = $request->files->get('profileImage');
            if ($imageFile) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profile_images';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $extension = $imageFile->guessExtension() ?: 'bin';
                $fileName = uniqid('profile_') . '.' . $extension;
                $imageFile->move($uploadDir, $fileName);
                $profile->setProfileImageName($fileName);
                $profile->setProfileImageSize($imageFile->getSize());
                $profile->setProfileImageUpdatedAt(new \DateTimeImmutable());
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'isVerified' => $user->isVerified(),
                    'roles' => $user->getRoles(),
                    'profile' => [
                        'displayName' => $profile->getDisplayName(),
                        'bio' => $profile->getBio(),
                        'location' => $profile->getLocation(),
                        'website' => $profile->getWebsite(),
                        'status' => $profile->getStatus(),
                        'isPublic' => $profile->isPublic(),
                        'image_url' => $profile->getProfileImageName() ? '/uploads/profile_images/' . $profile->getProfileImageName() : null,
                    ],
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to update user profile'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/images', name: 'api_images', methods: ['GET', 'OPTIONS'])]
    public function getUserImages(Request $request): JsonResponse
    {

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

    #[Route('/albums', name: 'api_albums', methods: ['GET'])]
    public function getUserAlbums(Request $request): JsonResponse
    {

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

    #[Route('/shares', name: 'api_shares', methods: ['GET'])]
    public function getUserShares(Request $request): JsonResponse
    {

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

    #[Route('/images/upload', name: 'api_images_upload', methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {

        try {
            $user = $this->authenticateUser($request);
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $uploadedFile = $request->files->get('image');
            if (!$uploadedFile) {
                return $this->json([
                    'success' => false,
                    'error' => 'No image file provided'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validate file type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validate file size (10MB max)
            $maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if ($uploadedFile->getSize() > $maxSize) {
                return $this->json([
                    'success' => false,
                    'error' => 'File size too large. Maximum size is 10MB.'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create image entity
            $image = new Image();
            $image->setUser($user);
            $image->setTitle($request->get('title', $uploadedFile->getClientOriginalName()));
            $image->setDescription($request->get('description', ''));

            // Handle album assignment if provided
            $albumId = $request->get('albumId');
            if ($albumId) {
                $album = $this->entityManager->getRepository(\App\Entity\Album::class)->find($albumId);
                if ($album && $album->getUser() === $user) {
                    $image->setAlbum($album);
                }
            }

            // Generate unique filename
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            // Set image properties
            $image->setImageName($newFilename);
            $image->setImageSize($uploadedFile->getSize());

            // Create images directory if it doesn't exist
            $imagesDir = $this->getParameter('kernel.project_dir') . '/images';
            if (!is_dir($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }

            // Move the file
            $uploadedFile->move($imagesDir, $newFilename);

            // Save to database
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image' => [
                    'id' => $image->getId(),
                    'title' => $image->getTitle(),
                    'description' => $image->getDescription(),
                    'imageName' => $image->getImageName(),
                    'imageSize' => $image->getImageSize(),
                    'createdAt' => $image->getCreatedAt()->format('Y-m-d H:i:s'),
                    'albumId' => $image->getAlbum() ? $image->getAlbum()->getId() : null,
                    'albumName' => $image->getAlbum() ? $image->getAlbum()->getName() : null
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to upload image: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




}
