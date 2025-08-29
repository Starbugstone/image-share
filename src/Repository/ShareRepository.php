<?php

namespace App\Repository;

use App\Entity\Share;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Share|null find($id, $lockMode = null, $lockVersion = null)
 * @method Share|null findOneBy(array $criteria, array $orderBy = null)
 * @method Share[]    findAll()
 * @method Share[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Share::class);
    }

    /**
     * Find shares by user (items shared by this user)
     */
    public function findBySharedBy(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.sharedBy = :user')
            ->setParameter('user', $user)
            ->orderBy('s.sharedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find shares received by user
     */
    public function findBySharedWith(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.sharedWith = :user')
            ->setParameter('user', $user)
            ->orderBy('s.sharedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if an image is already shared with a specific user
     */
    public function isImageSharedWith(Image $image, User $user): bool
    {
        $share = $this->createQueryBuilder('s')
            ->where('s.image = :image')
            ->andWhere('s.sharedWith = :user')
            ->setParameter('image', $image)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $share !== null;
    }

    /**
     * Check if an album is already shared with a specific user
     */
    public function isAlbumSharedWith(Album $album, User $user): bool
    {
        $share = $this->createQueryBuilder('s')
            ->where('s.album = :album')
            ->andWhere('s.sharedWith = :user')
            ->setParameter('album', $album)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $share !== null;
    }

    /**
     * Find all users an image is shared with
     */
    public function findUsersImageSharedWith(Image $image): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.sharedWith')
            ->where('s.image = :image')
            ->setParameter('image', $image)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all users an album is shared with
     */
    public function findUsersAlbumSharedWith(Album $album): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.sharedWith')
            ->where('s.album = :album')
            ->setParameter('album', $album)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find shares by type (image or album)
     */
    public function findByType(string $type): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        if ($type === 'image') {
            $queryBuilder->where('s.image IS NOT NULL');
        } elseif ($type === 'album') {
            $queryBuilder->where('s.album IS NOT NULL');
        }

        return $queryBuilder->orderBy('s.sharedAt', 'DESC')->getQuery()->getResult();
    }

    /**
     * Find recent shares
     */
    public function findRecentShares(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.sharedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find shares between two specific users
     */
    public function findSharesBetweenUsers(User $user1, User $user2): array
    {
        return $this->createQueryBuilder('s')
            ->where('(s.sharedBy = :user1 AND s.sharedWith = :user2) OR (s.sharedBy = :user2 AND s.sharedWith = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('s.sharedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
