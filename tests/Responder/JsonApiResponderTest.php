<?php

declare(strict_types=1);

namespace Oryx\Adr\Tests\Responder;

use PHPUnit\Framework\TestCase;
use Oryx\Adr\Responder\JsonApiResponder;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response;

class JsonApiResponderTest extends TestCase
{
    public function testRespondsWithJsonApiContentType(): void
    {
        $data = ['test' => 'data'];
        $responder = new JsonApiResponder($data);
        $response = $responder->respond();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('application/vnd.api+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($data, $body);
    }

    public function testHandlesNullData(): void
    {
        $responder = new JsonApiResponder(null);
        $response = $responder->respond();
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertNull($body);
    }

    public function testHandlesArrayData(): void
    {
        $data = [['id' => 1], ['id' => 2]];
        $responder = new JsonApiResponder($data);
        $response = $responder->respond();
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($data, $body);
    }
}
