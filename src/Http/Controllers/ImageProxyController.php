<?php

namespace Juzaweb\ImageProxy\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;
use Juzaweb\CMS\Http\Controllers\BackendController;

class ImageProxyController extends BackendController
{
    protected FilesystemAdapter|Filesystem $disk;

    protected int $cacheAge = 86400;

    public function __construct()
    {
        $this->disk = Storage::disk('local');
    }

    public function proxy(string $url, string|int $width, string|int $height, string $fileName)
    {
        $url = hex2bin($url);
        $url = openssl_decrypt($url, 'AES-128-ECB', config('image-proxy.proxy_token'));
        $url = urldecode(base64_decode($url));
        $format = $this->getFormat($url);
        $quality = 90;

        if ($width == 'auto' || (int)$width == 0) {
            $width = null;
        }

        if ($height == 'auto' || (int)$height == 0) {
            $height = null;
        }

        $aspectRatio = empty($width) || empty($height) ? fn ($constraint) => $constraint->aspectRatio() : null;
        try {
            $img = $this->getImageByUrl($url, $format);
        } catch (\Throwable $e) {
            return response('', 404);
        }

        if ($width || $height) {
            $img->resize($width, $height, $aspectRatio);
        }

        if (File::exists(storage_path('logo.png'))) {
            $img->insert(storage_path('logo.png'), 'bottom-right', 10, 10);
        }

        return $img->response($this->getFormat($fileName), $quality)
            ->setCache(['public' => true, 'max_age' => $this->cacheAge, 's_maxage' => $this->cacheAge]);
    }

    protected function getImageByUrl(string $url, string $format): InterventionImage
    {
        $hash = sha1($url);

        if (!$this->disk->exists('proxy-images')) {
            $this->disk->makeDirectory('proxy-images');
        }

        $cacheFile = $this->disk->path("proxy-images/{$hash}");
        if (file_exists($cacheFile)) {
            return Image::make($cacheFile);
        }

        $img = Image::make($url);
        $img->save($cacheFile, null, $format);
        return $img;
    }

    protected function getFormat(string $url): string
    {
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        return explode('?', $extension)[0] ?? 'jpg';
    }
}
