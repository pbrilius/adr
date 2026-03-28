<?php

declare(strict_types=1);

namespace Oryx\Adr\Tests\Responder;

use PHPUnit\Framework\TestCase;
use Oryx\Adr\Responder\ProblemDetailsResponder;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response;

class ProblemDetailsResponderTest extends TestCase
{
    public function testRespondsWithProblemDetailsContentType(): void
    {
        $responder = new ProblemDetailsResponder(
            'https://example.com/probs/out-of-credit',
            'You do not have enough credit.',
            403,
            'Your account has only 5 USD left.',
            'https://example.com/account/12345/msgs/abc',
            ['occurred' => ['latitude' => 40.1234, 'longitude' => -74.0056]]
        );
        $response = $responder->respond();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('application/problem+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(403, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('https://example.com/probs/out-of-credit', $body['type']);
        $this->assertEquals('You do not have enough credit.', $body['title']);
        $this->assertEquals(403, $body['status']);
        $this->assertEquals('Your account has only 5 USD left.', $body['detail']);
        $this->assertEquals('https://example.com/account/12345/msgs/abc', $body['instance']);
        $this->assertArrayHasKey('occurred', $body);
        $this->assertEquals(['latitude' => 40.1234, 'longitude' => -74.0056], $body['occurred']);
    }

    public function testHandlesMinimalProblemDetails(): void
    {
        $responder = new ProblemDetailsResponder(
            'https://example.com/probs/unknown',
            'Unknown error',
            500
        );
        $response = $responder->respond();
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('https://example.com/probs/unknown', $body['type']);
        $this->assertEquals('Unknown error', $body['title']);
        $this->assertEquals(500, $body['status']);
        $this->assertArrayNotHasKey('detail', $body);
        $this->assertArrayNotHasKey('instance', $body);
    }
}
