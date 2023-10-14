<?php

namespace Juzaweb\ImageProxy\Actions;

use Juzaweb\CMS\Abstracts\Action;

class ProxyAction extends Action
{
    /**
     * Execute the actions.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->addFilter('get_upload_url', [$this, 'changeUrlWithProxy'], 20, 4);
    }

    public function changeUrlWithProxy($url, $path, $default, $size): string
    {
        if (is_url($path)) {
            [$width, $height] = explode('x', $size ?? 'autoxauto');

            $proxyDomain = config('image-proxy.proxy_domain_url');
            $absolute = $proxyDomain === null;

            $token = urlencode($path);
            $token = base64_encode($token);
            $token = openssl_encrypt($token, 'AES-128-ECB', config('image-proxy.proxy_token'));
            $token = bin2hex($token);

            $url = route(
                'asset.image.proxy',
                [
                    $token,
                    $width,
                    $height,
                    jw_basename($path)
                ],
                $absolute
            );

            if ($proxyDomain) {
                return rtrim($proxyDomain, '/') . $url;
            }

            return $url;
        }

        return $url;
    }
}
