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
