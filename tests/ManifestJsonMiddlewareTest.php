<?php

declare(strict_types=1);

namespace Oryx\Adr\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Oryx\Adr\Middleware\ManifestJsonMiddleware;

class ManifestJsonMiddlewareTest extends TestCase
{
    public function testManifestJsonRequestReturnsJsonResponse(): void
    {
        $serverParams = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/manifest.json',
            'SCRIPT_NAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];

        $request = new ServerRequest(
            $serverParams,
            [],
            'https://example.com/manifest.json',
            'GET',
            new Stream('php://temp', 'r+'),
            [],
            [],
            [],
            null,
            '1.1'
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn(new Response('Default', 200, []));

        $middleware = new ManifestJsonMiddleware();
        $response = $middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/manifest+json', $response->getHeaderLine('Content-Type'));
        $this->assertJson($response->getBody()->getContents());
    }

    public function testManifestJsonRequestWithCustomValues(): void
    {
        $serverParams = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/manifest.json',
            'SCRIPT_NAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];

        $request = new ServerRequest(
            $serverParams,
            [],
            'https://example.com/manifest.json',
            'GET',
            new Stream('php://temp', 'r+'),
            [],
            [],
            [],
            null,
            '1.1'
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn(new Response('Default', 200, []));

        $middleware = new ManifestJsonMiddleware(
            'Custom PWA App',
            'CustomApp',
            'A custom PWA application',
            '/home',
            'fullscreen',
            '#000000',
            '#ffffff',
            [
                [
                    'src' => '/custom/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ]
            ]
        );
        $response = $middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/manifest+json', $response->getHeaderLine('Content-Type'));

        // Get the contents once to avoid consuming the stream twice
        $contents = $response->getBody()->getContents();
        $this->assertJson($contents);

        $manifest = json_decode($contents, true);
        
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('name', $manifest);
        $this->assertEquals('Custom PWA App', $manifest['name']);
        $this->assertEquals('CustomApp', $manifest['short_name']);
        $this->assertEquals('A custom PWA application', $manifest['description']);
        $this->assertEquals('/home', $manifest['start_url']);
        $this->assertEquals('fullscreen', $manifest['display']);
        $this->assertEquals('#000000', $manifest['background_color']);
        $this->assertEquals('#ffffff', $manifest['theme_color']);
        $this->assertIsArray($manifest['icons']);
        $this->assertCount(1, $manifest['icons']);
        $this->assertEquals('/custom/icon-192x192.png', $manifest['icons'][0]['src']);
        $this->assertEquals('192x192', $manifest['icons'][0]['sizes']);
        $this->assertEquals('image/png', $manifest['icons'][0]['type']);
    }

    public function testManifestJsonRequestFallbackToDefaultIcons(): void
    {
        $serverParams = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/manifest.json',
            'SCRIPT_NAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];

        $request = new ServerRequest(
            $serverParams,
            [],
            'https://example.com/manifest.json',
            'GET',
            new Stream('php://temp', 'r+'),
            [],
            [],
            [],
            null,
            '1.1'
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn(new Response('Default', 200, []));

        // Pass empty icons array to trigger fallback to defaults
        $middleware = new ManifestJsonMiddleware(
            'Test App',
            'TestApp',
            'Test description',
            '/test',
            'standalone',
            '#ffffff',
            '#000000',
            []
        );
        $response = $middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/manifest+json', $response->getHeaderLine('Content-Type'));

        // Get the contents once to avoid consuming the stream twice
        $contents = $response->getBody()->getContents();
        $this->assertJson($contents);

        $manifest = json_decode($contents, true);
        
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('name', $manifest);
        $this->assertEquals('Test App', $manifest['name']);
        $this->assertEquals('TestApp', $manifest['short_name']);
        $this->assertEquals('Test description', $manifest['description']);
        $this->assertEquals('/test', $manifest['start_url']);
        $this->assertEquals('standalone', $manifest['display']);
        $this->assertEquals('#ffffff', $manifest['background_color']);
        $this->assertEquals('#000000', $manifest['theme_color']);
        $this->assertIsArray($manifest['icons']);
        $this->assertNotEmpty($manifest['icons']);
        $this->assertArrayHasKey(0, $manifest['icons']);
        $this->assertArrayHasKey('src', $manifest['icons'][0]);
        // Check that we got the default icons (should be 8 icons)
        $this->assertCount(8, $manifest['icons']);
        $this->assertEquals('/icons/icon-72x72.png', $manifest['icons'][0]['src']);
        $this->assertEquals('72x72', $manifest['icons'][0]['sizes']);
        $this->assertEquals('image/png', $manifest['icons'][0]['type']);
        $this->assertEquals('/icons/icon-512x512.png', $manifest['icons'][7]['src']);
        $this->assertEquals('512x512', $manifest['icons'][7]['sizes']);
        $this->assertEquals('image/png', $manifest['icons'][7]['type']);
    }

    public function testNonManifestRequestDelegatesToHandler(): void
    {
        $serverParams = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/test',
            'SCRIPT_NAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];

        $request = new ServerRequest(
            $serverParams,
            [],
            'https://example.com/api/test',
            'GET',
            new Stream('php://temp', 'r+'),
            [],
            [],
            [],
            null,
            '1.1'
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = new Response('Custom Response', 200, ['X-Custom' => 'value']);
        $handler->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($expectedResponse);

        $middleware = new ManifestJsonMiddleware();
        $response = $middleware->process($request, $handler);

        $this->assertSame($expectedResponse, $response);
    }
}