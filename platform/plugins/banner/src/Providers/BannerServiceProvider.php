<?php

namespace Botble\Banner\Providers;

use Botble\Banner\Models\Banner;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Support\ServiceProvider;
use Botble\Banner\Repositories\Caches\BannerCacheDecorator;
use Botble\Banner\Repositories\Eloquent\BannerRepository;
use Botble\Banner\Repositories\Interfaces\BannerInterface;
use Botble\Base\Supports\Helper;
use Illuminate\Support\Facades\Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class BannerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(BannerInterface::class, function () {
            return new BannerCacheDecorator(new BannerRepository(new Banner));
        });

        Helper::autoload(__DIR__ . '/../../helpers');
    }

    public function boot()
    {
        $this->setNamespace('plugins/banner')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api']);

        Event::listen(RouteMatched::class, function () {

            dashboard_menu()->registerItem([
                'id'          => 'cms-plugins-banner',
                'priority'    => 100,
                'parent_id'   => null,
                'name'        => 'plugins/banner::banner.name',
                'icon'        => 'fa fa-images',
                'url'         => route('banner.index'),
                'permissions' => ['banner.index'],
            ]);
        });

        LanguageAdvancedManager::registerModule(Banner::class, [
            'name',
            'subtitle',
            'description',
        ]);
    }
}
