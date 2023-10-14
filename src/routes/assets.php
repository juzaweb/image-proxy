<?php

use Juzaweb\ImageProxy\Http\Controllers\ImageProxyController;

Route::get(
    '/assets/images/{url}/{width}/{height}/{name}',
    [ImageProxyController::class, 'proxy']
)->name('asset.image.proxy');
