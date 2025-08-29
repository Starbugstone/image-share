<?php

namespace App\Repository;

use App\Entity\UserProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserProfile>
 *
 * @method UserProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProfile[]    findAll()
 * @method UserProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProfile::class);
    }

    /**
     * Find profiles by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status)
            ->orderBy('p.lastSeen', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find online users
     */
    public function findOnlineUsers(): array
    {
        $fiveMinutesAgo = new \DateTimeImmutable('-5 minutes');
        
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.lastSeen > :fiveMinutesAgo')
            ->setParameter('status', 'online')
            ->setParameter('fiveMinutesAgo', $fiveMinutesAgo)
            ->orderBy('p.lastSeen', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Update user status to online
     */
    public function setUserOnline(int $userId): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.status', ':status')
            ->set('p.lastSeen', ':lastSeen')
            ->where('p.user = :userId')
            ->setParameter('status', 'online')
            ->setParameter('lastSeen', new \DateTimeImmutable())
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    /**
     * Update user status to offline
     */
    public function setUserOffline(int $userId): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.status', ':status')
            ->set('p.lastSeen', ':lastSeen')
            ->where('p.user = :userId')
            ->setParameter('status', 'offline')
            ->setParameter('lastSeen', new \DateTimeImmutable())
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    /**
     * Find public profiles
     */
    public function findPublicProfiles(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
