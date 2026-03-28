<?php

declare(strict_types=1);

namespace Oryx\Adr\Responder;

use Psr\Http\Message\ResponseInterface;

/**
 * Responder interface for the ADR pattern.
 * Responders are responsible for generating HTTP responses.
 */
interface ResponderInterface
{
    /**
     * Generate and return the HTTP response.
     *
     * @return ResponseInterface The HTTP response
     */
    public function respond(): ResponseInterface;
}
