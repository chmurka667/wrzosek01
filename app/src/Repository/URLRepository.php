<?php

/**
 * URL repository.
 */

namespace App\Repository;

use App\Dto\UrlListFiltersDto;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class URLRepository.
 *
 * Repository for Url entity.
 *
 * @extends ServiceEntityRepository<Url>
 */
class URLRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    /**
     * Query all records.
     *
     * @param UrlListFiltersDto $filters          Filters
     * @param User|null         $user             User entity (optional)
     * @param bool              $restrictToAuthor Restrict results to author
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(UrlListFiltersDto $filters, ?User $user = null, bool $restrictToAuthor = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('url')
            ->select(
                'partial url.{id, createdAt, originalUrl, shortenedUrl, email, clicks}',
                'partial tags.{id, title}'
            )
            ->leftJoin('url.tags', 'tags');

        if ($restrictToAuthor && $user instanceof User) {
            $qb->andWhere('url.user = :user')
                ->setParameter('user', $user);
        }

        return $this->applyFiltersToList($qb, $filters);
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($url);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($url);
        $this->_em->flush();
    }

    /**
     * Find by shortened Url.
     *
     * @param string $shortenedUrl Shortened URL
     *
     * @return Url|null Url entity
     */
    public function findByShortenedUrl(string $shortenedUrl): ?Url
    {
        return $this->findOneBy(['shortenedUrl' => $shortenedUrl]);
    }

    /**
     * Query URLs by user.
     *
     * @param User $user User entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByUser(User $user): QueryBuilder
    {
        return $this->queryAll(new UrlListFiltersDto(null), $user, true);
    }

    /**
     * Query by clicks.
     *
     * @return QueryBuilder Query builder
     */
    public function queryByClicks(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial url.{id, createdAt, originalUrl, shortenedUrl, email, clicks}')
            ->orderBy('url.createdAt', 'DESC');
    }

    /**
     * Check daily limit.
     *
     * @param string             $clientIp Client IP address
     * @param \DateTimeInterface $today    Start of day (inclusive)
     * @param \DateTimeInterface $tomorrow Next day (exclusive)
     *
     * @return int Number of created URLs
     */
    public function checkDailyLimit(string $clientIp, \DateTimeInterface $today, \DateTimeInterface $tomorrow): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.ipAddress = :ip')
            ->andWhere('u.createdAt >= :today')
            ->andWhere('u.createdAt < :tomorrow')
            ->setParameter('ip', $clientIp)
            ->setParameter('today', $today, \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)
            ->setParameter('tomorrow', $tomorrow, \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Apply filters to list.
     *
     * @param QueryBuilder      $queryBuilder Query builder
     * @param UrlListFiltersDto $filters      Filters
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, UrlListFiltersDto $filters): QueryBuilder
    {
        if ($filters->tag instanceof Tag) {
            $queryBuilder
                ->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        return $queryBuilder;
    }

    /**
     * Query builder.
     *
     * @param QueryBuilder|null $queryBuilder Existing query builder or null
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }
}
