<?php

namespace Botble\Blog;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('comments_translations');
    }
}
