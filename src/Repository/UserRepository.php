<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Find users by email or username
     */
    public function findByEmailOrUsername(string $search): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.email LIKE :search')
            ->orWhere('u.username LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find verified users
     */
    public function findVerifiedUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.isVerified = :verified')
            ->setParameter('verified', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users created after a specific date
     */
    public function findUsersCreatedAfter(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.createdAt > :date')
            ->setParameter('date', $date)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search users for sharing (excludes current user and unverified users)
     */
    public function searchUsersForSharing(string $query, int $currentUserId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->andWhere('u.isVerified = :verified')
            ->andWhere('u.username LIKE :query')
            ->setParameter('currentUserId', $currentUserId)
            ->setParameter('verified', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.username', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all available users for sharing (excludes current user and unverified users)
     */
    public function getAvailableUsersForSharing(int $currentUserId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->andWhere('u.isVerified = :verified')
            ->setParameter('currentUserId', $currentUserId)
            ->setParameter('verified', true)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
