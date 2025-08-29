<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Share;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Find comments by author
     */
    public function findByAuthor(User $author): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.author = :author')
            ->setParameter('author', $author)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find comments for an image
     */
    public function findByImage(Image $image): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.image = :image')
            ->setParameter('image', $image)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find comments for a share
     */
    public function findByShare(Share $share): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.share = :share')
            ->setParameter('share', $share)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find comments visible to a user
     * This includes comments where the user is either the author or the owner
     */
    public function findVisibleToUser(User $user, ?int $limit = null): array
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.image', 'i')
            ->leftJoin('c.share', 's')
            ->leftJoin('s.image', 'si')
            ->leftJoin('s.album', 'sa')
            ->where('c.author = :user')
            ->orWhere('i.user = :user')
            ->orWhere('si.user = :user')
            ->orWhere('sa.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC');
            
        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }
        
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Find comments for images owned by user
     */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.image', 'i')
            ->leftJoin('c.share', 's')
            ->leftJoin('s.image', 'si')
            ->leftJoin('s.album', 'sa')
            ->where('i.user = :owner')
            ->orWhere('si.user = :owner')
            ->orWhere('sa.user = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent comments
     */
    public function findRecentComments(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find comments by date range
     */
    public function findByDateRange(User $user, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.author = :user')
            ->andWhere('c.createdAt BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find comments between two specific users
     */
    public function findBetweenUsers(User $user1, User $user2): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.image', 'i')
            ->leftJoin('c.share', 's')
            ->leftJoin('s.sharedBy', 'sb')
            ->leftJoin('s.sharedWith', 'sw')
            ->where('(c.author = :user1 AND i.user = :user2) OR (c.author = :user2 AND i.user = :user1)')
            ->orWhere('(c.author = :user1 AND sb = :user2 AND sw = :user1) OR (c.author = :user2 AND sb = :user1 AND sw = :user2)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
