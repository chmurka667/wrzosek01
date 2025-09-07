<?php

/**
 * Url entity.
 */

namespace App\Entity;

use App\Repository\URLRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Url.
 */
#[ORM\Entity(repositoryClass: URLRepository::class)]
class Url
{
    /**
     * Primary key.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Original URL.
     *
     * @var string|null
     */
    #[ORM\Column(name: 'original_url', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255, maxMessage: 'url_cannot_be_longer')]
    #[Assert\Url(protocols: ['http', 'https'])]
    private ?string $originalUrl = null;

    /**
     * Shortened URL.
     *
     * @var string|null
     */
    #[ORM\Column(name: 'shortened_url', length: 255)]
    private ?string $shortenedUrl = null;

    /**
     * Created at.
     *
     * @var DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt = null;

    /**
     * Email.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * Owner user.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'uRLs')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * Number of clicks.
     *
     * @var int
     */
    #[ORM\Column]
    private int $clicks = 0;

    /**
     * Tags.
     *
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private Collection $tags;

    /**
     * IP address that created the URL.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    /**
     * Blocked until.
     *
     * @var DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $blockedUntil = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Get ID.
     *
     * @return int|null URL ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get original URL.
     *
     * @return string|null Original URL
     */
    public function getOriginalUrl(): ?string
    {
        return $this->originalUrl;
    }

    /**
     * Set original URL.
     *
     * @param string $originalUrl Original URL
     *
     * @return static
     */
    public function setOriginalUrl(string $originalUrl): static
    {
        $this->originalUrl = $originalUrl;

        return $this;
    }

    /**
     * Get shortened URL.
     *
     * @return string|null Shortened URL
     */
    public function getShortenedUrl(): ?string
    {
        return $this->shortenedUrl;
    }

    /**
     * Set shortened URL.
     *
     * @param string $shortenedUrl Shortened URL
     *
     * @return static
     */
    public function setShortenedUrl(string $shortenedUrl): static
    {
        $this->shortenedUrl = $shortenedUrl;

        return $this;
    }

    /**
     * Get creation date.
     *
     * @return DateTimeImmutable|null Creation timestamp
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set creation date.
     *
     * @param DateTimeImmutable $createdAt Creation timestamp
     *
     * @return static
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null Email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email Email
     *
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get owner user.
     *
     * @return User|null User entity
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set owner user.
     *
     * @param User|null $user User entity
     *
     * @return static
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get number of clicks.
     *
     * @return int Click count
     */
    public function getClicks(): int
    {
        return $this->clicks;
    }

    /**
     * Set number of clicks.
     *
     * @param int $clicks Click count
     *
     * @return static
     */
    public function setClicks(int $clicks): static
    {
        $this->clicks = $clicks;

        return $this;
    }

    /**
     * Get tags.
     *
     * @return Collection<int, Tag> Tags
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return static
     */
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return static
     */
    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * Get IP address.
     *
     * @return string|null IP address
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * Set IP address.
     *
     * @param string|null $ipAddress IP address
     *
     * @return static
     */
    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get blocked until.
     *
     * @return DateTimeInterface|null Blocked until
     */
    public function getBlockedUntil(): ?DateTimeInterface
    {
        return $this->blockedUntil;
    }

    /**
     * Set blocked until.
     *
     * @param DateTimeInterface|null $blockedUntil Blocked until
     *
     * @return static
     */
    public function setBlockedUntil(?DateTimeInterface $blockedUntil): static
    {
        $this->blockedUntil = $blockedUntil;

        return $this;
    }

    /**
     * Check if URL is currently blocked.
     *
     * @return bool True if blocked, false otherwise
     */
    public function isBlocked(): bool
    {
        return null !== $this->blockedUntil && new DateTime() < $this->blockedUntil;
    }
}
