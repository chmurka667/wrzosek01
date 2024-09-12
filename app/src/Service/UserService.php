<?php
/**
 * User service.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Entity;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\String\ByteString;


/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
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
     * @param UserRepository     $userRepository User repository
     */
    public function __construct(private readonly UserRepository $userRepository)
    {
    }


    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void
    {
        $this->userRepository->delete($user);
    }


}