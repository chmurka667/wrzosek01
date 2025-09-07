<?php

/**
 * Tag entity.
 */

namespace App\Entity;

use App\Repository\TagRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Tag.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Created at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * Slug.
     */
    #[ORM\Column(length: 64)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug = null;

    /**
     * Title.
     */
    #[ORM\Column(length: 64)]
    private ?string $title = null;

    /**
     * Get ID.
     *
     * @return int|null Tag ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get creation date.
     *
     * @return DateTimeInterface|null Creation timestamp
     */
    public function getCreatedAt(): ?DateTimeInterface
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
     * Get update date.
     *
     * @return DateTimeInterface|null Update timestamp
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Set update date.
     *
     * @param DateTimeImmutable $updatedAt Update timestamp
     *
     * @return static
     */
    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string|null Slug
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Set slug.
     *
     * @param string $slug Slug
     *
     * @return static
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title Title
     *
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
