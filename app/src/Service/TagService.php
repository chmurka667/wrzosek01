<?php
/**
 * Category service.
 */
namespace App\Service;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\Tag;
use App\Repository\TaskRepository;
use App\Repository\CategoryRepository;
use App\Service\CategoryServiceInterface;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class CategoryService.
 */
class TagService implements TagServiceInterface
{
    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tag|null Tag entity
     */
    public function findOneByTitle(string $title): ?Tag
    {
        return $this->tagRepository->findOneByTitle($title);
    }
}