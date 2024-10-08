<?php

namespace App\Repository;

use App\Entity\Url;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
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
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial url.{id, createdAt, original_url, shortened_url, email}'
            )
            ->orderBy('url.createdAt', 'DESC');
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     * @throws ORMException
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
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Url $url): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($url);
        $this->_em->flush();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }

    public function findByShortenedUrl(string $shortened_url): ?Url
    {
        return $this->findOneBy(['shortened_url' => $shortened_url]);
    }

    /**
     * Query urls by user.
     *
     * @param User $user User entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByUser(User $user): QueryBuilder
    {
        $queryBuilder = $this->queryAll();

        $queryBuilder->andWhere('url.users = :user')
            ->setParameter('user', $user);

        return $queryBuilder;
    }

    public function queryByClicks(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial url.{id, createdAt, original_url, shortened_url, email, clicks}'
            )
            ->orderBy('url.createdAt', 'DESC');
    }
}
