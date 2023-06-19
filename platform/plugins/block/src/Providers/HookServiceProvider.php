<?php

namespace Botble\Block\Providers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Block\Repositories\Interfaces\BlockInterface;
use Botble\Shortcode\Compilers\Shortcode;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (function_exists('shortcode')) {
            add_shortcode(
                'static-block',
                trans('plugins/block::block.static_block_short_code_name'),
                trans('plugins/block::block.static_block_short_code_description'),
                [$this, 'render']
            );

            shortcode()->setAdminConfig('static-block', function ($attributes, $content) {
                $blocks = $this->app->make(BlockInterface::class)
                    ->pluck('name', 'alias', ['status' => BaseStatusEnum::PUBLISHED]);

                $data = compact('blocks', 'attributes', 'content');

                return view('plugins/block::partials.short-code-admin-config', $data)->render();
            });
        }
    }

    /**
     * @param Shortcode $shortcode
     * @return string|null
     * @throws BindingResolutionException
     */
    public function render(Shortcode $shortcode): ?string
    {
        $block = $this->app->make(BlockInterface::class)
            ->getFirstBy([
                'alias'  => $shortcode->alias,
                'status' => BaseStatusEnum::PUBLISHED,
            ]);

        return $block ? $block->content : null;
    }
}
