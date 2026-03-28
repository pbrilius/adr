<?php

declare(strict_types=1);

namespace Oryx\Adr\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Oryx\Adr\Domain\DomainInterface;

/**
 * Action interface for the ADR pattern.
 * Actions contain minimal logic to coordinate between Domain and Responder.
 * Actions are invokable as middleware or controllers.
 */
interface ActionInterface
{
    /**
     * Execute the action with the given domain and return the responder class name.
     *
     * @param DomainInterface $domain The domain object containing business logic
     * @return string           The responder class name
     */
    public function execute(DomainInterface $domain): string;

    /**
     * Handle the request and produce a response.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param callable $next The next middleware (optional)
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ): ResponseInterface;
}
