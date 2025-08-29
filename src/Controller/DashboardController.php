<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Album;
use App\Entity\Share;
use App\Entity\Comment;
use App\Repository\ImageRepository;
use App\Repository\AlbumRepository;
use App\Repository\ShareRepository;
use App\Repository\CommentRepository;
use App\Service\SharingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_dashboard')]
    public function index(
        ImageRepository $imageRepository,
        AlbumRepository $albumRepository,
        ShareRepository $shareRepository,
        CommentRepository $commentRepository,
        SharingService $sharingService
    ): Response {
        $user = $this->getUser();

        // Check if user is verified
        if (!$user->isVerified()) {
            return $this->render('dashboard/verification_required.html.twig', [
                'user' => $user,
                'resend_url' => $this->generateUrl('app_verify_resend', ['id' => $user->getId()])
            ]);
        }

        // Get user's statistics
        $stats = [
            'total_images' => $imageRepository->count(['user' => $user]),
            'total_albums' => $albumRepository->count(['user' => $user]),
            'shared_items' => $shareRepository->count(['sharedBy' => $user]),
            'received_shares' => $shareRepository->count(['sharedWith' => $user]),
            'recent_comments' => $commentRepository->findVisibleToUser($user, 5),
        ];

        // Get recent activity
        $recentImages = $imageRepository->findByUser($user, ['createdAt' => 'DESC'], 6);
        $recentAlbums = $albumRepository->findByUser($user, ['createdAt' => 'DESC'], 6);
        $recentShares = $shareRepository->findBySharedWith($user, ['sharedAt' => 'DESC'], 6);

        $availableUsers = $sharingService->getAvailableUsers($user);

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'recent_images' => $recentImages,
            'recent_albums' => $recentAlbums,
            'recent_shares' => $recentShares,
            'available_users' => $availableUsers,
        ]);
    }

    #[Route('/images', name: 'app_dashboard_images')]
    public function images(ImageRepository $imageRepository, SharingService $sharingService): Response
    {
        $user = $this->getUser();
        $images = $imageRepository->findByUser($user);
        $availableUsers = $sharingService->getAvailableUsers($user);

        return $this->render('dashboard/images.html.twig', [
            'images' => $images,
            'available_users' => $availableUsers,
        ]);
    }

    #[Route('/albums', name: 'app_dashboard_albums')]
    public function albums(AlbumRepository $albumRepository, SharingService $sharingService): Response
    {
        $user = $this->getUser();
        $albums = $albumRepository->findByUser($user);
        $availableUsers = $sharingService->getAvailableUsers($user);

        return $this->render('dashboard/albums.html.twig', [
            'albums' => $albums,
            'available_users' => $availableUsers,
        ]);
    }

    #[Route('/shares', name: 'app_dashboard_shares')]
    public function shares(ShareRepository $shareRepository): Response
    {
        $user = $this->getUser();

        $sharedByMe = $shareRepository->findBySharedBy($user);
        $sharedWithMe = $shareRepository->findBySharedWith($user);

        return $this->render('dashboard/shares.html.twig', [
            'shared_by_me' => $sharedByMe,
            'shared_with_me' => $sharedWithMe,
        ]);
    }

    #[Route('/comments', name: 'app_dashboard_comments')]
    public function comments(CommentRepository $commentRepository): Response
    {
        $user = $this->getUser();
        $comments = $commentRepository->findVisibleToUser($user);

        return $this->render('dashboard/comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
