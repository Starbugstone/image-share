<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    /**
     * Find images by user
     */
    public function findByUser(User $user, array $orderBy = ['createdAt' => 'DESC']): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.user = :user')
            ->setParameter('user', $user)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find images by album
     */
    public function findByAlbum(Album $album): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.album = :album')
            ->setParameter('album', $album)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find images not in any album for a user
     */
    public function findUnassignedImages(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.user = :user')
            ->andWhere('i.album IS NULL')
            ->setParameter('user', $user)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent images
     */
    public function findRecentImages(int $limit = 10): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find images shared with a user
     */
    public function findImagesSharedWith(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.shares', 's')
            ->where('s.sharedWith = :user')
            ->setParameter('user', $user)
            ->orderBy('s.sharedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search images by title or description
     */
    public function searchImages(string $query, User $user): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.user = :user')
            ->andWhere('i.title LIKE :query OR i.description LIKE :query')
            ->setParameter('user', $user)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find images by date range
     */
    public function findByDateRange(User $user, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.user = :user')
            ->andWhere('i.createdAt BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
