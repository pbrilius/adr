<?php

declare(strict_types=1);

namespace Oryx\Adr\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Oryx\Adr\Domain\DomainInterface;
use Oryx\Adr\Responder\ResponderFactory;
use Oryx\Adr\Responder\ResponderInterface;

/**
 * Abstract base class for Actions in the ADR pattern.
 * Provides a skeleton implementation that can be extended.
 * Actions are invokable as middleware or controllers.
 */
abstract class AbstractAction implements ActionInterface
{
    /**
     * @var DomainInterface The domain object containing business logic
     */
    private DomainInterface $domain;

    /**
     * @var ResponderFactory Factory for creating responder instances
     */
    private ResponderFactory $responderFactory;

    /**
     * Constructor.
     *
     * @param DomainInterface $domain The domain object containing business logic
     * @param ResponderFactory $responderFactory Factory for creating responder instances
     */
    public function __construct(DomainInterface $domain, ResponderFactory $responderFactory)
    {
        $this->domain = $domain;
        $this->responderFactory = $responderFactory;
    }

    /**
     * Execute the action with the given domain and return the responder class name.
     * This method must be implemented by concrete action classes.
     *
     * @param DomainInterface $domain The domain object containing business logic
     * @return string The responder class name
     */
    abstract public function execute(DomainInterface $domain): string;

    /**
     * Handle the request and produce a response.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param callable $next The next middleware (optional)
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null): ResponseInterface
    {
        // Execute the action to get the responder class name
        $responderClass = $this->execute($this->domain);
        
        // Create the responder instance using the factory
        $responderInstance = $this->responderFactory->create($responderClass);
        
        // Generate and return the response
        return $responderInstance->respond();
    }
}