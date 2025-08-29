<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $content;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\ManyToOne(targetEntity: Image::class, inversedBy: 'comments')]
    private $image;

    #[ORM\ManyToOne(targetEntity: Share::class, inversedBy: 'comments')]
    private $share;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $author;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getShare(): ?Share
    {
        return $this->share;
    }

    public function setShare(?Share $share): self
    {
        $this->share = $share;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Check if this comment is visible to a specific user
     * Comments are only visible to the author and the owner of the shared item
     */
    public function isVisibleTo(User $user): bool
    {
        // The author can always see their own comments
        if ($this->author === $user) {
            return true;
        }

        // Check if the user is the owner of the shared item
        if ($this->image) {
            return $this->image->getUser() === $user;
        }

        if ($this->share) {
            $sharedItem = $this->share->getSharedItem();
            if ($sharedItem && method_exists($sharedItem, 'getUser')) {
                return $sharedItem->getUser() === $user;
            }
        }

        return false;
    }

    /**
     * Get the associated item (either image or shared item)
     */
    public function getAssociatedItem()
    {
        return $this->image ?? $this->share?->getSharedItem();
    }

    /**
     * Get the type of the associated item
     */
    public function getAssociatedItemType(): string
    {
        if ($this->image) {
            return 'image';
        }
        if ($this->share) {
            return $this->share->getSharedItemType();
        }
        return '';
    }
}
