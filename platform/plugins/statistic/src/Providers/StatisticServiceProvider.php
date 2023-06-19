<?php

namespace Botble\Statistic\Providers;

use Botble\Statistic\Models\Statistic;
use Illuminate\Support\ServiceProvider;
use Botble\Statistic\Repositories\Caches\StatisticCacheDecorator;
use Botble\Statistic\Repositories\Eloquent\StatisticRepository;
use Botble\Statistic\Repositories\Interfaces\StatisticInterface;
use Illuminate\Support\Facades\Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class StatisticServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(StatisticInterface::class, function () {
            return new StatisticCacheDecorator(new StatisticRepository(new Statistic));
        });

        $this->setNamespace('plugins/statistic')->loadHelpers();
    }

    public function boot()
    {
        $this
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api']);

//        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
//            if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
//                // Use language v2
//                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Statistic::class, [
//                    'name',
//                ]);
//            } else {
//                // Use language v1
//                $this->app->booted(function () {
//                    \Language::registerModule([Statistic::class]);
//                });
//            }
//        }

        Event::listen(RouteMatched::class, function () {
            dashboard_menu()->registerItem([
                'id'          => 'cms-plugins-statistic',
                'priority'    => 5,
                'parent_id'   => null,
                'name'        => 'plugins/statistic::statistic.name',
                'icon'        => 'fa fa-list',
                'url'         => route('statistic.index'),
                'permissions' => ['statistic.index'],
            ]);
        });
    }
}
