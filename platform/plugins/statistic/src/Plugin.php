<?php

namespace Botble\Statistic;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('statistics');
        Schema::dropIfExists('statistics_translations');
    }
}
