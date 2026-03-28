<?php

declare(strict_types=1);

namespace Oryx\Adr\Responder;

use Psr\Http\Message\ResponseInterface;

/**
 * Factory for creating responder instances.
 */
interface ResponderFactory
{
    /**
     * Create a responder instance.
     *
     * @param string $responderClass The responder class name
     * @return ResponderInterface The responder instance
     */
    public function create(string $responderClass): ResponderInterface;
}