<?php

/**
 * Url service.
 */

namespace App\Service;

use App\Dto\UrlListFiltersDto;
use App\Dto\UrlListInputFiltersDto;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Repository\URLRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UrlService.
 */
class UrlService implements UrlServiceInterface
{
    /**
     * Items per page.
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param URLRepository       $urlRepository URL repository
     * @param PaginatorInterface  $paginator     Paginator
     * @param TagServiceInterface $tagService    Tag service
     * @param TagRepository       $tagRepository Tag repository
     */
    public function __construct(private readonly URLRepository $urlRepository, private readonly PaginatorInterface $paginator, private readonly TagServiceInterface $tagService, private readonly TagRepository $tagRepository)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int                    $page    Page
     * @param UrlListInputFiltersDto $filters Input filters
     * @param User|null              $user    Current user
     *
     * @return PaginationInterface<string, mixed> Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedList(int $page, UrlListInputFiltersDto $filters, ?User $user = null): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles(), true);
        $restrictToAuthor = $user && !$isAdmin;

        return $this->paginator->paginate(
            $this->urlRepository->queryAll($filters, $user, $restrictToAuthor),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList'   => ['url.id', 'url.createdAt', 'url.originalUrl', 'url.shortenedUrl', 'url.email', 'url.clicks'],
                'defaultSortFieldName' => 'url.clicks',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save.
     *
     * @param Url $url Url
     */
    public function save(Url $url): void
    {
        $this->urlRepository->save($url);
    }

    /**
     * Delete.
     *
     * @param Url $url Url
     */
    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }

    /**
     * Generate unique short URL.
     *
     * @param string $host Host
     *
     * @return string Short URL
     *
     * @throws \Exception When random_bytes fails
     */
    public function generateUniqueShortUrl(string $host): string
    {
        do {
            $shortenedUrl = rtrim($host, '/').'/'.bin2hex(random_bytes(3));
            $existingUrl = $this->urlRepository->findByShortenedUrl($shortenedUrl);
        } while ($existingUrl);

        return $shortenedUrl;
    }

    /**
     * Find by shortened URL.
     *
     * @param string $slug Slug without slash
     * @param string $host Host base URL
     *
     * @return Url|null Url
     */
    public function findByShortenedUrl(string $slug, string $host): ?Url
    {
        $shortenedUrl = rtrim($host, '/').'/'.$slug;

        return $this->urlRepository->findByShortenedUrl($shortenedUrl);
    }

    /**
     * Find tag by title.
     *
     * @param string $title Title
     *
     * @return Tag|null Tag
     */
    public function findOneByTitle(string $title): ?Tag
    {
        return $this->tagRepository->findOneByTitle($title);
    }

    /**
     * Check daily limit.
     *
     * @param string $ipAddress IP
     *
     * @return int Count
     */
    public function checkDailyLimit(string $ipAddress): int
    {
        $today = new \DateTime();

        $tomorrow = clone $today;
        $tomorrow->add(new \DateInterval('P1D'));

        return $this->urlRepository->checkDailyLimit($ipAddress, $today, $tomorrow);
    }

    /**
     * Prepare filters.
     *
     * @param UrlListInputFiltersDto $filters Input
     *
     * @return UrlListFiltersDto Filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(UrlListInputFiltersDto $filters): UrlListFiltersDto
    {
        return new UrlListFiltersDto(
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null
        );
    }
}
