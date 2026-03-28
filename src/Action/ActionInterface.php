<?php

declare(strict_types=1);

namespace Oryx\Adr\Action;

use Psr\Http\Message\ResponseInterface;
use Oryx\Adr\Domain\DomainInterface;
use Oryx\Adr\Responder\ResponderInterface;

/**
 * Action interface for the ADR pattern.
 * Actions contain minimal logic to coordinate between Domain and Responder.
 */
interface ActionInterface
{
    /**
     * Execute the action with the given domain and return a responder.
     *
     * @param DomainInterface $domain The domain object containing business logic
     * @return ResponderInterface The responder responsible for generating the response
     */
    public function execute(DomainInterface $domain): ResponderInterface;
}