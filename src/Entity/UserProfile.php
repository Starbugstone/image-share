<?php

namespace App\Entity;

use App\Repository\UserProfileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
class UserProfile
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\OneToOne(inversedBy: 'profile')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profileImageName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $profileImageSize = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $profileImageUpdatedAt = null;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'offline'])]
    private string $status = 'offline';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastSeen = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $socialLinks = [];

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isPublic = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->userId = $user->getId();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->lastSeen = new \DateTimeImmutable();
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getProfileImageName(): ?string
    {
        return $this->profileImageName;
    }

    public function setProfileImageName(?string $profileImageName): self
    {
        $this->profileImageName = $profileImageName;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getProfileImageSize(): ?int
    {
        return $this->profileImageSize;
    }

    public function setProfileImageSize(?int $profileImageSize): self
    {
        $this->profileImageSize = $profileImageSize;
        return $this;
    }

    public function getProfileImageUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->profileImageUpdatedAt;
    }

    public function setProfileImageUpdatedAt(?\DateTimeImmutable $profileImageUpdatedAt): self
    {
        $this->profileImageUpdatedAt = $profileImageUpdatedAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getLastSeen(): ?\DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function setLastSeen(\DateTimeImmutable $lastSeen): self
    {
        $this->lastSeen = $lastSeen;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getSocialLinks(): array
    {
        return $this->socialLinks;
    }

    public function setSocialLinks(array $socialLinks): self
    {
        $this->socialLinks = $socialLinks;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function addSocialLink(string $platform, string $url): self
    {
        $this->socialLinks[$platform] = $url;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function removeSocialLink(string $platform): self
    {
        unset($this->socialLinks[$platform]);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateLastSeen(): self
    {
        $this->lastSeen = new \DateTimeImmutable();
        return $this;
    }

    public function isOnline(): bool
    {
        if ($this->status === 'online') {
            // Check if user was active in the last 5 minutes
            $fiveMinutesAgo = new \DateTimeImmutable('-5 minutes');
            return $this->lastSeen > $fiveMinutesAgo;
        }
        return false;
    }

    public function getStatusDisplay(): string
    {
        if ($this->isOnline()) {
            return 'online';
        }
        
        if ($this->lastSeen) {
            $now = new \DateTimeImmutable();
            $diff = $now->diff($this->lastSeen);
            
            if ($diff->days > 0) {
                return 'offline';
            } elseif ($diff->h > 0) {
                return 'away';
            } else {
                return 'recently';
            }
        }
        
        return 'offline';
    }

    public function getStatusColor(): string
    {
        return match($this->getStatusDisplay()) {
            'online' => 'success',
            'away' => 'warning',
            'recently' => 'info',
            default => 'secondary'
        };
    }
}
