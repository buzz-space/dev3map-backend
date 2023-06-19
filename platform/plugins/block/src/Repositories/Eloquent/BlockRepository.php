<?php

namespace Botble\Block\Repositories\Eloquent;

use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Botble\Block\Repositories\Interfaces\BlockInterface;
use Illuminate\Support\Str;

class BlockRepository extends RepositoriesAbstract implements BlockInterface
{
    /**
     * {@inheritDoc}
     */
    public function createSlug(?string $name, ?int $id): string
    {
        $slug = Str::slug($name);
        $index = 1;
        $baseSlug = $slug;
        while ($this->model->where('alias', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $baseSlug . '-' . $index++;
        }

        if (empty($slug)) {
            $slug = time();
        }

        $this->resetModel();

        return $slug;
    }
}
