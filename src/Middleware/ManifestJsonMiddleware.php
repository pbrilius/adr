<?php

declare(strict_types=1);

namespace Oryx\Adr\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * ManifestJsonMiddleware serves PWA manifest.json requests.
 * This middleware handles requests for the web app manifest and returns
 * a JSON response with PWA manifest properties.
 */
class ManifestJsonMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response.
     *
     * @param ServerRequestInterface $request  The request
     * @param RequestHandlerInterface $handler The request handler
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        
        // Handle manifest.json requests
        if ($uri === '/manifest.json' || $uri === '/manifest.webmanifest') {
            $manifest = [
                'name' => 'Oryx PWA App',
                'short_name' => 'OryxApp',
                'description' => 'A PWA built with Oryx ADR',
                'start_url' => '/',
                'display' => 'standalone',
                'background_color' => '#ffffff',
                'theme_color' => '#000000',
                'icons' => [
                    [
                        'src' => '/icons/icon-72x72.png',
                        'sizes' => '72x72',
                        'type' => 'image/png'
                    ],
                    [
                        'src' => '/icons/icon-96x96.png',
                        'sizes' => '96x96',
                        'type' => 'image/png'
                    ],
                    [
                        'src' => '/icons/icon-128x128.png',
                        'sizes' => '128x128',
                        'type' => 'image/png'
                    ],
                    [
                        'src' => '/icons/icon-144x144.png',
                        'sizes' => '144x144',
                        'type' => 'image/png'
                    ],
                    [
                        'src' => '/icons/icon-152x152.png',
                        'sizes' => '152x152',
                        'type' => 'image/png'
                    ],
                    [
                        'src' => '/icons/icon-192x192.png',
                        'sizes' => '192x192',
                        'type' => 'image/png'
                    ],
                    [
                        'src' => '/icons/icon-384x384.png',
                        'sizes' => '384x384',
                        'type' => 'image/png'
                    ],
                    [
                        'src' => '/icons/icon-512x512.png',
                        'sizes' => '512x512',
                        'type' => 'image/png'
                    ]
                ]
            ];
            
            return new JsonResponse($manifest, 200, [
                'Content-Type' => 'application/manifest+json'
            ]);
        }
        
        // For all other requests, delegate to the next middleware
        return $handler->handle($request);
    }
}