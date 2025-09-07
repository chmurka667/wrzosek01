<?php

/**
 * Url list input filters DTO.
 */

namespace App\Dto;

/**
 * Class UrlListInputFiltersDto.
 */
readonly class UrlListInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param int|null $tagId Tag identifier
     */
    public function __construct(public ?int $tagId = null)
    {
    }
}
