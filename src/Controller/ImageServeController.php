<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImageServeController extends AbstractController
{
    private string $imagesDirectory;

    public function __construct(string $imagesDirectory)
    {
        $this->imagesDirectory = $imagesDirectory;
    }

    #[Route('/secure-image/{id}', name: 'app_secure_image')]
    #[IsGranted('ROLE_USER')]
    public function serveImage(Image $image, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Check if user can access this image
        $canAccess = $this->canAccessImage($image, $user);

        if (!$canAccess) {
            throw $this->createAccessDeniedException('You do not have permission to view this image.');
        }

        $imagePath = $this->imagesDirectory . '/' . $image->getImageName();

        if (!file_exists($imagePath)) {
            throw $this->createNotFoundException('Image file not found.');
        }

        // Return image with proper headers
        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', $this->getMimeType($imagePath));
        $response->headers->set('Content-Disposition', 'inline; filename="' . $image->getImageName() . '"');
        $response->setCache([
            'etag' => md5_file($imagePath),
            'last_modified' => new \DateTime('@' . filemtime($imagePath)),
            'max_age' => 3600, // 1 hour cache
            's_maxage' => 3600,
            'public' => true,
        ]);

        return $response;
    }

    #[Route('/api/secure-image/{id}', name: 'api_secure_image', methods: ['GET', 'OPTIONS'])]
    public function serveImageWithToken(Image $image, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Handle preflight OPTIONS for CORS
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse();
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5175');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            return $response;
        }

        // Extract token from Authorization header or query param
        $authHeader = $request->headers->get('Authorization');
        $token = null;
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }
        if (!$token) {
            $token = $request->query->get('token');
        }

        if (!$token) {
            return new JsonResponse(['success' => false, 'error' => 'Authentication token required'], Response::HTTP_UNAUTHORIZED);
        }

        // Find user by API token
        $user = $entityManager->getRepository(\App\Entity\User::class)
            ->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        // Authorization check (reuse same logic as secure route)
        if (!$this->canAccessImage($image, $user)) {
            return new JsonResponse(['success' => false, 'error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $imagePath = $this->imagesDirectory . '/' . $image->getImageName();
        if (!file_exists($imagePath)) {
            return new JsonResponse(['success' => false, 'error' => 'Image not found'], Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', $this->getMimeType($imagePath));
        $response->headers->set('Content-Disposition', 'inline; filename="' . $image->getImageName() . '"');
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5175');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->setCache([
            'etag' => md5_file($imagePath),
            'last_modified' => new \DateTime('@' . filemtime($imagePath)),
            'max_age' => 3600,
            's_maxage' => 3600,
            'public' => true,
        ]);

        return $response;
    }

    #[Route('/secure-album-image/{albumId}/{imageName}', name: 'app_secure_album_image')]
    #[IsGranted('ROLE_USER')]
    public function serveAlbumImage(int $albumId, string $imageName, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Find the album
        $album = $entityManager->getRepository(Album::class)->find($albumId);
        if (!$album) {
            throw $this->createNotFoundException('Album not found.');
        }

        // Check if user can access the album
        $canAccess = $this->canAccessAlbum($album, $user);
        if (!$canAccess) {
            throw $this->createAccessDeniedException('You do not have permission to view this album.');
        }

        // Find the image in the album
        $image = null;
        foreach ($album->getImages() as $albumImage) {
            if ($albumImage->getImageName() === $imageName) {
                $image = $albumImage;
                break;
            }
        }

        if (!$image) {
            throw $this->createNotFoundException('Image not found in album.');
        }

        $imagePath = $this->imagesDirectory . '/' . $imageName;

        if (!file_exists($imagePath)) {
            throw $this->createNotFoundException('Image file not found.');
        }

        // Return image with proper headers
        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', $this->getMimeType($imagePath));
        $response->headers->set('Content-Disposition', 'inline; filename="' . $imageName . '"');
        $response->setCache([
            'etag' => md5_file($imagePath),
            'last_modified' => new \DateTime('@' . filemtime($imagePath)),
            'max_age' => 3600,
            's_maxage' => 3600,
            'public' => true,
        ]);

        return $response;
    }

    private function canAccessImage(Image $image, $user): bool
    {
        // Owner can always access
        if ($user && $image->getUser() === $user) {
            return true;
        }

        // Check if image is shared with user
        foreach ($image->getShares() as $share) {
            if ($share->getSharedWith() === $user) {
                return true;
            }
        }

        // Check if image is in a public album
        $album = $image->getAlbum();
        if ($album && $album->getIsPublic()) {
            return true;
        }

        return false;
    }

    private function canAccessAlbum(Album $album, $user): bool
    {
        // Owner can always access
        if ($user && $album->getUser() === $user) {
            return true;
        }

        // Check if album is public
        if ($album->getIsPublic()) {
            return true;
        }

        // Check if album is shared with user
        foreach ($album->getShares() as $share) {
            if ($share->getSharedWith() === $user) {
                return true;
            }
        }

        return false;
    }

    private function getMimeType(string $filePath): string
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
