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
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $shortName;

    /**
     * @var string
     */
    private string $description;

    /**
     * @var string
     */
    private string $startUrl;

    /**
     * @var string
     */
    private string $display;

    /**
     * @var string
     */
    private string $backgroundColor;

    /**
     * @var string
     */
    private string $themeColor;

    /**
     * @var array
     */
    private array $icons;

    /**
     * Constructor.
     *
     * @param string $name          The application name
     * @param string $shortName     The short application name
     * @param string $description   The application description
     * @param string $startUrl      The start URL
     * @param string $display       The display mode
     * @param string $backgroundColor The background color
     * @param string $themeColor    The theme color
     * @param array  $icons         The icons array
     */
    public function __construct(
        string $name = 'Oryx PWA App',
        string $shortName = 'OryxApp',
        string $description = 'A PWA built with Oryx ADR',
        string $startUrl = '/',
        string $display = 'standalone',
        string $backgroundColor = '#ffffff',
        string $themeColor = '#000000',
        array $icons = []
    ) {
        $this->name = $name;
        $this->shortName = $shortName;
        $this->description = $description;
        $this->startUrl = $startUrl;
        $this->display = $display;
        $this->backgroundColor = $backgroundColor;
        $this->themeColor = $themeColor;
        $this->icons = $icons;
    }

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
            // Use provided icons or fall back to defaults
            $icons = $this->icons;
            if (empty($icons)) {
                $icons = [
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
                ];
            }

            $manifest = [
                'name' => $this->name,
                'short_name' => $this->shortName,
                'description' => $this->description,
                'start_url' => $this->startUrl,
                'display' => $this->display,
                'background_color' => $this->backgroundColor,
                'theme_color' => $this->themeColor,
                'icons' => $icons
            ];

            return new JsonResponse($manifest, 200, [
                'Content-Type' => 'application/manifest+json'
            ]);
        }

        // For all other requests, delegate to the next middleware
        return $handler->handle($request);
    }
}
