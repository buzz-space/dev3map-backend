<?php

namespace Botble\Block\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface BlockInterface extends RepositoryInterface
{
    /**
     * @param string|null $name
     * @param int|null $id
     * @return string
     */
    public function createSlug(?string $name, ?int $id): string;
}
