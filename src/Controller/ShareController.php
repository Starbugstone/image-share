<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Album;
use App\Entity\Share;
use App\Service\SharingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/share')]
#[IsGranted('ROLE_USER')]
class ShareController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}
    #[Route('/image/{id}', name: 'app_share_image')]
    public function shareImage(
        Image $image,
        Request $request,
        SharingService $sharingService
    ): Response {
        if ($request->isMethod('POST')) {
            $usernames = $request->request->get('usernames', '');
            $message = $request->request->get('message', '');

            try {
                $result = $sharingService->shareImage($image, $this->getUser(), $usernames, $message);
                
                if ($result['success']) {
                    $this->addFlash('success', "Image shared with {$result['shared_count']} user(s) successfully!");
                } else {
                    $this->addFlash('warning', 'No new users to share with.');
                }

                // Show any errors that occurred
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error);
                }

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error sharing image: ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_image_show', ['id' => $image->getId()]);
        }

        $availableUsers = $sharingService->getAvailableUsers($this->getUser());
        $sharedUsers = $sharingService->getImageSharedUsers($image);

        return $this->render('share/image.html.twig', [
            'image' => $image,
            'available_users' => $availableUsers,
            'shared_users' => $sharedUsers,
        ]);
    }

    #[Route('/album/{id}', name: 'app_share_album')]
    public function shareAlbum(
        Album $album,
        Request $request,
        SharingService $sharingService
    ): Response {
        if ($request->isMethod('POST')) {
            $usernames = $request->request->get('usernames', '');
            $message = $request->request->get('message', '');

            try {
                $result = $sharingService->shareAlbum($album, $this->getUser(), $usernames, $message);
                
                if ($result['success']) {
                    $this->addFlash('success', "Album shared with {$result['shared_count']} user(s) successfully!");
                } else {
                    $this->addFlash('warning', 'No new users to share with.');
                }

                // Show any errors that occurred
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error);
                }

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error sharing album: ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        $availableUsers = $sharingService->getAvailableUsers($this->getUser());
        $sharedUsers = $sharingService->getAlbumSharedUsers($album);

        return $this->render('share/album.html.twig', [
            'album' => $album,
            'available_users' => $availableUsers,
            'shared_users' => $sharedUsers,
        ]);
    }

    #[Route('/remove/image/{image}/user/{user}', name: 'app_share_remove_image')]
    public function removeImageShare(
        Image $image,
        User $user,
        SharingService $sharingService
    ): Response {
        try {
            $share = $sharingService->findShareByImageAndUser($image, $user);
            if ($share) {
                $sharingService->removeShare($share, $this->getUser());
                $this->addFlash('success', 'Share removed successfully!');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error removing share: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_share_image', ['id' => $image->getId()]);
    }

    #[Route('/remove/album/{album}/user/{user}', name: 'app_share_remove_album')]
    public function removeAlbumShare(
        Album $album,
        User $user,
        SharingService $sharingService
    ): Response {
        try {
            $share = $sharingService->findShareByAlbumAndUser($album, $user);
            if ($share) {
                $sharingService->removeShare($share, $this->getUser());
                $this->addFlash('success', 'Share removed successfully!');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error removing share: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_share_album', ['id' => $album->getId()]);
    }

    #[Route('/quick-share', name: 'app_quick_share', methods: ['POST'])]
    public function quickShare(Request $request, SharingService $sharingService): Response
    {
        $type = $request->request->get('type'); // 'image' or 'album'
        $id = $request->request->get('id');
        $usernames = $request->request->get('usernames', '');
        $message = $request->request->get('message', '');

        try {
            if ($type === 'image') {
                $image = $this->entityManager->getRepository(Image::class)->find($id);
                if (!$image || $image->getUser() !== $this->getUser()) {
                    throw new \Exception('Image not found or access denied.');
                }
                
                // Extract user IDs from usernames (comma-separated)
                $usernameList = array_map('trim', explode(',', $usernames));
                $userIds = [];
                foreach ($usernameList as $username) {
                    $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        $userIds[] = $user->getId();
                    }
                }
                
                if (empty($userIds)) {
                    throw new \Exception('No valid users found to share with.');
                }
                
                $result = $sharingService->shareImageWithUserIds($image, $this->getUser(), $userIds, $message);
            } elseif ($type === 'album') {
                $album = $this->entityManager->getRepository(Album::class)->find($id);
                if (!$album || $album->getUser() !== $this->getUser()) {
                    throw new \Exception('Album not found or access denied.');
                }
                
                // Extract user IDs from usernames (comma-separated)
                $usernameList = array_map('trim', explode(',', $usernames));
                $userIds = [];
                foreach ($usernameList as $username) {
                    $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
                    if ($user) {
                        $userIds[] = $user->getId();
                    }
                }
                
                if (empty($userIds)) {
                    throw new \Exception('No valid users found to share with.');
                }
                
                $result = $sharingService->shareAlbumWithUserIds($album, $this->getUser(), $userIds, $message);
            } else {
                throw new \Exception('Invalid share type.');
            }

            if ($result['success']) {
                return $this->json([
                    'success' => true,
                    'message' => "Shared with {$result['shared_count']} user(s) successfully!",
                    'errors' => $result['errors']
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'No new users to share with.',
                    'errors' => $result['errors']
                ]);
            }

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error sharing: ' . $e->getMessage()
            ], 400);
        }
    }
}
