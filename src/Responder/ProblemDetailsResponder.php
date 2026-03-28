<?php

declare(strict_types=1);

namespace Oryx\Adr\Responder;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Laminas\Diactoros\Stream;

/**
 * Responder that outputs Problem Details for HTTP APIs (RFC 7807).
 */
class ProblemDetailsResponder implements ResponderInterface
{
    private string $type;
    private string $title;
    private int $status;
    private ?string $detail = null;
    private ?string $instance = null;
    private array $extensions = [];

    public function __construct(
        string $type,
        string $title,
        int $status,
        ?string $detail = null,
        ?string $instance = null,
        array $extensions = []
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->status = $status;
        $this->detail = $detail;
        $this->instance = $instance;
        $this->extensions = $extensions;
    }

    public function respond(): ResponseInterface
    {
        $problem = [
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
        ];

        if ($this->detail !== null) {
            $problem['detail'] = $this->detail;
        }

        if ($this->instance !== null) {
            $problem['instance'] = $this->instance;
        }

        if (!empty($this->extensions)) {
            $problem = array_merge($problem, $this->extensions);
        }

        $json = json_encode($problem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode Problem Details response');
        }

        $stream = new Stream('php://temp', 'w+');
        $stream->write($json);
        $stream->rewind();

        return new \Laminas\Diactoros\Response(
            $stream,
            $this->status,
            [
                'Content-Type' => 'application/problem+json; charset=utf-8',
            ]
        );
    }
}