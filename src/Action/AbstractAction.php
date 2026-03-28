<?php

declare(strict_types=1);

namespace Oryx\Adr\Action;

use Oryx\Adr\Domain\DomainInterface;
use Oryx\Adr\Responder\ResponderInterface;

/**
 * Abstract base class for Actions in the ADR pattern.
 * Provides a skeleton implementation that can be extended.
 */
abstract class AbstractAction implements ActionInterface
{
    /**
     * Execute the action with the given domain and return a responder.
     * This method must be implemented by concrete action classes.
     *
     * @param DomainInterface $domain The domain object containing business logic
     * @return ResponderInterface The responder responsible for generating the response
     */
    abstract public function execute(DomainInterface $domain): ResponderInterface;
}