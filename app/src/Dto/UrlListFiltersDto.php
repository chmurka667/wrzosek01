<?php

/**
 * Url list filters DTO.
 */

namespace App\Dto;

use App\Entity\Tag;

/**
 * Class UrlListFiltersDto.
 */
class UrlListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Tag|null $tag Tag entity
     */
    public function __construct(public readonly ?Tag $tag)
    {
    }
}
