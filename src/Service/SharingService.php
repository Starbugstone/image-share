<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\Album;
use App\Entity\Share;
use App\Entity\User;
use App\Repository\ShareRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SharingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private ShareRepository $shareRepository
    ) {}

    /**
     * Share an image with multiple users
     */
    public function shareImage(Image $image, User $owner, string $usernames, string $message = ''): array
    {
        // Verify ownership
        if ($image->getUser() !== $owner) {
            throw new AccessDeniedException('You cannot share this image.');
        }

        $usernameList = array_map('trim', explode(',', $usernames));
        $sharedCount = 0;
        $errors = [];

        foreach ($usernameList as $username) {
            if (empty($username)) continue;

            try {
                $result = $this->shareImageWithUser($image, $owner, $username, $message);
                if ($result['success']) {
                    $sharedCount++;
                } else {
                    $errors[] = $result['error'];
                }
            } catch (\Exception $e) {
                $errors[] = "Error sharing with {$username}: " . $e->getMessage();
            }
        }

        if ($sharedCount > 0) {
            $this->entityManager->flush();
        }

        return [
            'shared_count' => $sharedCount,
            'errors' => $errors,
            'success' => $sharedCount > 0
        ];
    }

    /**
     * Share an image with multiple users by user IDs
     */
    public function shareImageWithUserIds(Image $image, User $owner, array $userIds, string $message = ''): array
    {
        // Verify ownership
        if ($image->getUser() !== $owner) {
            throw new AccessDeniedException('You cannot share this image.');
        }

        $sharedCount = 0;
        $errors = [];

        foreach ($userIds as $userId) {
            try {
                $result = $this->shareImageWithUserId($image, $owner, $userId, $message);
                if ($result['success']) {
                    $sharedCount++;
                } else {
                    $errors[] = $result['error'];
                }
            } catch (\Exception $e) {
                $errors[] = "Error sharing with user ID {$userId}: " . $e->getMessage();
            }
        }

        if ($sharedCount > 0) {
            $this->entityManager->flush();
        }

        return [
            'shared_count' => $sharedCount,
            'errors' => $errors,
            'success' => $sharedCount > 0
        ];
    }

    /**
     * Share an album with multiple users
     */
    public function shareAlbum(Album $album, User $owner, string $usernames, string $message = ''): array
    {
        // Verify ownership
        if ($album->getUser() !== $owner) {
            throw new AccessDeniedException('You cannot share this album.');
        }

        $usernameList = array_map('trim', explode(',', $usernames));
        $sharedCount = 0;
        $errors = [];

        foreach ($usernameList as $username) {
            if (empty($username)) continue;

            try {
                $result = $this->shareAlbumWithUser($album, $owner, $username, $message);
                if ($result['success']) {
                    $sharedCount++;
                } else {
                    $errors[] = $result['error'];
                }
            } catch (\Exception $e) {
                $errors[] = "Error sharing with {$username}: " . $e->getMessage();
            }
        }

        if ($sharedCount > 0) {
            $this->entityManager->flush();
        }

        return [
            'shared_count' => $sharedCount,
            'errors' => $errors,
            'success' => $sharedCount > 0
        ];
    }

    /**
     * Share an album with multiple users by user IDs
     */
    public function shareAlbumWithUserIds(Album $album, User $owner, array $userIds, string $message = ''): array
    {
        // Verify ownership
        if ($album->getUser() !== $owner) {
            throw new AccessDeniedException('You cannot share this album.');
        }

        $sharedCount = 0;
        $errors = [];

        foreach ($userIds as $userId) {
            try {
                $result = $this->shareAlbumWithUserId($album, $owner, $userId, $message);
                if ($result['success']) {
                    $sharedCount++;
                } else {
                    $errors[] = $result['error'];
                }
            } catch (\Exception $e) {
                $errors[] = "Error sharing with user ID {$userId}: " . $e->getMessage();
            }
        }

        if ($sharedCount > 0) {
            $this->entityManager->flush();
        }

        return [
            'shared_count' => $sharedCount,
            'errors' => $errors,
            'success' => $sharedCount > 0
        ];
    }

    /**
     * Share an image with a specific user
     */
    private function shareImageWithUser(Image $image, User $owner, string $username, string $message): array
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        
        if (!$user) {
            return ['success' => false, 'error' => "User '{$username}' not found."];
        }

        if ($user === $owner) {
            return ['success' => false, 'error' => "Cannot share with yourself."];
        }

        if (!$user->isVerified()) {
            return ['success' => false, 'error' => "User '{$username}' is not verified."];
        }

        // Check if already shared
        if ($this->shareRepository->isImageSharedWith($image, $user)) {
            return ['success' => false, 'error' => "Already shared with '{$username}'."];
        }

        $share = new Share();
        $share->setImage($image);
        $share->setSharedBy($owner);
        $share->setSharedWith($user);
        $share->setMessage($message);

        $this->entityManager->persist($share);

        return ['success' => true, 'error' => null];
    }

    /**
     * Share an album with a specific user
     */
    private function shareAlbumWithUser(Album $album, User $owner, string $username, string $message): array
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        
        if (!$user) {
            return ['success' => false, 'error' => "User '{$username}' not found."];
        }

        if ($user === $owner) {
            return ['success' => false, 'error' => "Cannot share with yourself."];
        }

        if (!$user->isVerified()) {
            return ['success' => false, 'error' => "User '{$username}' is not verified."];
        }

        // Check if already shared
        if ($this->shareRepository->isAlbumSharedWith($album, $user)) {
            return ['success' => false, 'error' => "Already shared with '{$username}'."];
        }

        $share = new Share();
        $share->setAlbum($album);
        $share->setSharedBy($owner);
        $share->setSharedWith($user);
        $share->setMessage($message);

        $this->entityManager->persist($share);

        return ['success' => true, 'error' => null];
    }

    /**
     * Share an image with a specific user by user ID
     */
    private function shareImageWithUserId(Image $image, User $owner, int $userId, string $message): array
    {
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return ['success' => false, 'error' => "User with ID {$userId} not found."];
        }

        if ($user === $owner) {
            return ['success' => false, 'error' => "Cannot share with yourself."];
        }

        if (!$user->isVerified()) {
            return ['success' => false, 'error' => "User '{$user->getUsername()}' is not verified."];
        }

        // Check if already shared
        if ($this->shareRepository->isImageSharedWith($image, $user)) {
            return ['success' => false, 'error' => "Already shared with '{$user->getUsername()}'."];
        }

        $share = new Share();
        $share->setImage($image);
        $share->setSharedBy($owner);
        $share->setSharedWith($user);
        $share->setMessage($message);

        $this->entityManager->persist($share);

        return ['success' => true, 'error' => null];
    }

    /**
     * Share an album with a specific user by user ID
     */
    private function shareAlbumWithUserId(Album $album, User $owner, int $userId, string $message): array
    {
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return ['success' => false, 'error' => "User with ID {$userId} not found."];
        }

        if ($user === $owner) {
            return ['success' => false, 'error' => "Cannot share with yourself."];
        }

        if (!$user->isVerified()) {
            return ['success' => false, 'error' => "User '{$user->getUsername()}' is not verified."];
        }

        // Check if already shared
        if ($this->shareRepository->isAlbumSharedWith($album, $user)) {
            return ['success' => false, 'error' => "Already shared with '{$user->getUsername()}'."];
        }

        $share = new Share();
        $share->setAlbum($album);
        $share->setSharedWith($user);
        $share->setSharedBy($owner);
        $share->setMessage($message);

        $this->entityManager->persist($share);

        return ['success' => true, 'error' => null];
    }

    /**
     * Get available users for sharing (excluding current user)
     */
    public function getAvailableUsers(User $currentUser): array
    {
        return $this->userRepository->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->andWhere('u.isVerified = :verified')
            ->setParameter('currentUser', $currentUser)
            ->setParameter('verified', true)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get users an image is shared with
     */
    public function getImageSharedUsers(Image $image): array
    {
        return $this->shareRepository->findUsersImageSharedWith($image);
    }

    /**
     * Get users an album is shared with
     */
    public function getAlbumSharedUsers(Album $album): array
    {
        return $this->shareRepository->findUsersAlbumSharedWith($album);
    }

    /**
     * Remove a share
     */
    public function removeShare(Share $share, User $currentUser): bool
    {
        // Only the person who shared or received the share can remove it
        if ($share->getSharedBy() !== $currentUser && $share->getSharedWith() !== $currentUser) {
            throw new AccessDeniedException('You cannot remove this share.');
        }

        $this->entityManager->remove($share);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get shares by user (items shared by this user)
     */
    public function getSharesByUser(User $user): array
    {
        return $this->shareRepository->findBySharedBy($user);
    }

    /**
     * Get shares received by user
     */
    public function getSharesReceivedByUser(User $user): array
    {
        return $this->shareRepository->findBySharedWith($user);
    }

    /**
     * Find share by image and user
     */
    public function findShareByImageAndUser(Image $image, User $user): ?Share
    {
        return $this->shareRepository->findOneBy([
            'image' => $image,
            'sharedWith' => $user,
            'sharedBy' => $image->getUser()
        ]);
    }

    /**
     * Find share by album and user
     */
    public function findShareByAlbumAndUser(Album $album, User $user): ?Share
    {
        return $this->shareRepository->findOneBy([
            'album' => $album,
            'sharedWith' => $user,
            'sharedBy' => $album->getUser()
        ]);
    }
}
