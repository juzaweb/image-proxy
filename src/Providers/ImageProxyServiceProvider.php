<?php

namespace Juzaweb\ImageProxy\Providers;

use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Support\ServiceProvider;
use Juzaweb\ImageProxy\Actions\ProxyAction;

class ImageProxyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        ActionRegister::register([ProxyAction::class]);
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->mergeConfigFrom(__DIR__ .'/../../config/image-proxy.php', 'image-proxy');
    }
}
