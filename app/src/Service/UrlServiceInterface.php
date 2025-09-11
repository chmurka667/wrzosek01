<?php

/**
 * Url service interface.
 */

namespace App\Service;

use App\Dto\UrlListInputFiltersDto;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlServiceInterface.
 */
interface UrlServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int                    $page    Page number
     * @param UrlListInputFiltersDto $filters Filters
     * @param User|null              $user    Users
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, UrlListInputFiltersDto $filters, ?User $user = null): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void;

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void;

    /**
     * Generate a unique shortened URL.
     *
     * @param string $host Host part
     *
     * @return string Unique shortened URL
     *
     * @throws \Exception When random_bytes fails
     */
    public function generateUniqueShortUrl(string $host): string;

    /**
     * Find Url by slug and host.
     *
     * @param string $slug Slug
     * @param string $host Host
     *
     * @return ?Url Url entity
     */
    public function findByShortenedUrl(string $slug, string $host): ?Url;

    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tag|null Tag entity
     */
    public function findOneByTitle(string $title): ?Tag;

    /**
     * Check daily limit.
     *
     * @param string $ipAddress IP address
     *
     * @return int Number of URLs created today by this IP
     */
    public function checkDailyLimit(string $ipAddress): int;
}
