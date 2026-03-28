<?php

declare(strict_types=1);

namespace Oryx\Adr\Responder;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Laminas\Diactoros\Stream;

/**
 * Responder that outputs JSON:API format.
 *
 * This responder expects data to be already formatted according to JSON:API specification.
 * For convenience, static factory methods are provided to create common JSON:API structures.
 */
class JsonApiResponder implements ResponderInterface
{
    private mixed $data;
    private int $statusCode;
    private array $headers;

    public function __construct(mixed $data, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function respond(): ResponseInterface
    {
        $json = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode JSON:API response');
        }

        $stream = new Stream('php://temp', 'w+');
        $stream->write($json);
        $stream->rewind();

        $responseHeaders = [
            'Content-Type' => 'application/vnd.api+json; charset=utf-8',
        ];

        // Merge custom headers
        foreach ($this->headers as $name => $value) {
            $responseHeaders[$name] = $value;
        }

        return new \Laminas\Diactoros\Response(
            $stream,
            $this->statusCode,
            $responseHeaders
        );
    }

    /**
     * Create a JSON:API responder for a single resource.
     *
     * @param string $type The resource type (e.g., 'users')
     * @param mixed $id The resource ID
     * @param array $attributes The resource attributes
     * @param array|null $relationships The resource relationships (optional)
     * @param array|null $links The resource links (optional)
     * @return static
     */
    public static function singleResource(
        string $type,
        $id,
        array $attributes,
        ?array $relationships = null,
        ?array $links = null
    ): self {
        $data = [
            'type' => $type,
            'id' => (string)$id,
            'attributes' => $attributes,
        ];

        if ($relationships !== null) {
            $data['relationships'] = $relationships;
        }

        if ($links !== null) {
            $data['links'] = $links;
        }

        return new self(['data' => $data]);
    }

    /**
     * Create a JSON:API responder for a collection of resources.
     *
     * @param string $type The resource type (e.g., 'users')
     * @param array $resources Array of resources, each containing 'id', 'attributes', etc.
     * @param array|null $links Collection links (e.g., pagination)
     * @param array|null $included Included resources
     * @return static
     */
    public static function resourceCollection(
        string $type,
        array $resources,
        ?array $links = null,
        ?array $included = null
    ): self {
        $data = [];
        foreach ($resources as $resource) {
            $data[] = [
                'type' => $type,
                'id' => (string)$resource['id'],
                'attributes' => $resource['attributes'] ?? [],
                'relationships' => $resource['relationships'] ?? null,
                'links' => $resource['links'] ?? null,
            ];
            // Remove null values
            foreach ($data[sizeof($data) - 1] as $key => $value) {
                if ($value === null) {
                    unset($data[sizeof($data) - 1][$key]);
                }
            }
        }

        $result = ['data' => $data];

        if ($links !== null) {
            $result['links'] = $links;
        }

        if ($included !== null) {
            $result['included'] = $included;
        }

        return new self($result);
    }

    /**
     * Create a JSON:API responder for an empty collection.
     *
     * @param string $type The resource type
     * @param array|null $links Collection links (e.g., pagination)
     * @return static
     */
    public static function emptyCollection(string $type, ?array $links = null): self
    {
        return self::resourceCollection($type, [], $links);
    }

    /**
     * Create a JSON:API responder for no content (e.g., after DELETE).
     *
     * @return static
     */
    public static function noContent(): self
    {
        return new self(null, 204);
    }
}
