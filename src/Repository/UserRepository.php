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

    /**
     * Fetch all users with aggregated statistics
     *
     * Returns each user along with counts of their images, albums,
     * and how many images/albums they have shared.
     *
     * @return array<array{0: User, total_images: string, total_albums: string, shared_images: string, shared_albums: string}>
     */
    public function findAllWithStats(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->addSelect('COUNT(DISTINCT i.id) AS total_images')
            ->addSelect('COUNT(DISTINCT a.id) AS total_albums')
            ->addSelect('SUM(CASE WHEN s.image IS NOT NULL THEN 1 ELSE 0 END) AS shared_images')
            ->addSelect('SUM(CASE WHEN s.album IS NOT NULL THEN 1 ELSE 0 END) AS shared_albums')
            ->leftJoin('u.images', 'i')
            ->leftJoin('u.albums', 'a')
            ->leftJoin('u.sharedItems', 's')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
}
