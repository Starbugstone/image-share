<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * Find albums by user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find public albums
     */
    public function findPublicAlbums(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublic = :public')
            ->setParameter('public', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find albums shared with a user
     */
    public function findAlbumsSharedWith(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.shares', 's')
            ->where('s.sharedWith = :user')
            ->setParameter('user', $user)
            ->orderBy('s.sharedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search albums by name or description
     */
    public function searchAlbums(string $query, User $user): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.name LIKE :query OR a.description LIKE :query')
            ->setParameter('user', $user)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find albums with most images
     */
    public function findMostPopulatedAlbums(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->select('a, COUNT(i.id) as imageCount')
            ->leftJoin('a.images', 'i')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->groupBy('a.id')
            ->orderBy('imageCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find albums created after a specific date
     */
    public function findAlbumsCreatedAfter(User $user, \DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.createdAt > :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
