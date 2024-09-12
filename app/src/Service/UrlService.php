<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use App\Repository\URLRepository;
use Doctrine\ORM\Mapping\Entity;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Random\RandomException;
use Symfony\Component\String\ByteString;


/**
 * Class UrlService.
 */
class UrlService implements UrlServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param URLRepository     $URLRepository Url repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(private readonly URLRepository $URLRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $user = null): PaginationInterface
    {
        if ($user === null || $user->getId() === null || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->paginator->paginate(
                $this->URLRepository->queryByClicks(),
                $page,
                self::PAGINATOR_ITEMS_PER_PAGE
            );
        }
        else {
            return $this->paginator->paginate(
                $this->URLRepository->queryByUser($user),
                $page,
                self::PAGINATOR_ITEMS_PER_PAGE
            );
        }
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        $this->URLRepository->save($url);
    }

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        $this->URLRepository->delete($url);
    }

    /**
     * @throws RandomException
     */
    public function generateUniqueShortUrl(string $host): string
    {
        do {
            $shortenedUrl = $host . '/' . bin2hex(random_bytes(3));
            $existingUrl = $this->URLRepository->findOneBy(['shortened_url' => $shortenedUrl]);
        } while ($existingUrl);

        return $shortenedUrl;
    }

    public function findByShortenedUrl(string $slug, string $host): Url
    {
        $shortenedUrl = $host . '/' . $slug;
        $shortenedUrlPick = $this->URLRepository->findOneBy(['shortened_url' => $shortenedUrl]);
        return $shortenedUrlPick;
    }


}