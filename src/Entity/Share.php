<?php

namespace App\Entity;

use App\Repository\ShareRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ShareRepository::class)]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'unique_image_share', columns: ['image_id', 'shared_with_id']),
    new ORM\UniqueConstraint(name: 'unique_album_share', columns: ['album_id', 'shared_with_id'])
])]
class Share
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $sharedAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private $message;

    #[ORM\ManyToOne(targetEntity: Image::class, inversedBy: 'shares')]
    private $image;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'shares')]
    private $album;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sharedItems')]
    #[ORM\JoinColumn(nullable: false)]
    private $sharedBy;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'receivedShares')]
    #[ORM\JoinColumn(nullable: false)]
    private $sharedWith;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'share', orphanRemoval: true)]
    private $comments;

    public function __construct()
    {
        $this->sharedAt = new \DateTimeImmutable();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSharedAt(): ?\DateTimeImmutable
    {
        return $this->sharedAt;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
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

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function getSharedBy(): ?User
    {
        return $this->sharedBy;
    }

    public function setSharedBy(?User $sharedBy): self
    {
        $this->sharedBy = $sharedBy;

        return $this;
    }

    public function getSharedWith(): ?User
    {
        return $this->sharedWith;
    }

    public function setSharedWith(?User $sharedWith): self
    {
        $this->sharedWith = $sharedWith;

        return $this;
    }

    /**
     * Get the shared item (either image or album)
     */
    public function getSharedItem()
    {
        return $this->image ?? $this->album;
    }

    /**
     * Get the type of the shared item
     */
    public function getSharedItemType(): string
    {
        if ($this->image) {
            return 'image';
        }
        if ($this->album) {
            return 'album';
        }
        return '';
    }

    /**
     * Get the title of the shared item
     */
    public function getSharedItemTitle(): string
    {
        $item = $this->getSharedItem();
        return $item ? $item->getTitle() : '';
    }

    /**
     * Check if this share is for an image
     */
    public function isImageShare(): bool
    {
        return $this->image !== null;
    }

    /**
     * Check if this share is for an album
     */
    public function isAlbumShare(): bool
    {
        return $this->album !== null;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Comment[]
     */
    public function getComments(): \Doctrine\Common\Collections\Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setShare($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getShare() === $this) {
                $comment->setShare(null);
            }
        }

        return $this;
    }
}
