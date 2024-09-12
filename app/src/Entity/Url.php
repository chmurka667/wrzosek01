<?php

namespace App\Entity;

use App\Repository\URLRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=URLRepository::class)
 */
class Url
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     */
    private ?int $id = null;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     */
    public ?string $original_url = null;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     */
    public ?string $shortened_url = null;

    /**
     * @var DateTimeImmutable|null
     *
     * @ORM\Column(type="datetime_immutable")
     * @Gedmo\Timestampable(on="create")
     */
    private ?\DateTimeImmutable $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     */
    private ?string $email = null;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(inversedBy="uRLs")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?User $users = null;

    /**
     * @var int
     *
     * @ORM\Column
     */
    private int $clicks = 0;

    /**
     * @var Collection<int, Tag>
     *
     * @ORM\ManyToMany(targetEntity=Tag::class, fetch="EXTRA_LAZY", orphanRemoval=true)
     * @ORM\JoinTable(name="urls_tags")
     */
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getOriginalUrl(): ?string
    {
        return $this->original_url;
    }

    /**
     * @param string $original_url
     * @return static
     */
    public function setOriginalUrl(string $original_url): static
    {
        $this->original_url = $original_url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShortenedUrl(): ?string
    {
        return $this->shortened_url;
    }

    /**
     * @param string $shortened_url
     * @return static
     */
    public function setShortenedUrl(string $shortened_url): static
    {
        $this->shortened_url = $shortened_url;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUsers(): ?User
    {
        return $this->users;
    }

    /**
     * @param User|null $users
     * @return static
     */
    public function setUsers(?User $users): static
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return int
     */
    public function getClicks(): ?int
    {
        return $this->clicks;
    }

    /**
     * @param int $clicks
     * @return static
     */
    public function setClicks(int $clicks): static
    {
        $this->clicks = $clicks;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
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
     * @param Tag $tag
     * @return static
     */
    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
