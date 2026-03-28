<?php

declare(strict_types=1);

namespace Oryx\Adr\Domain;

/**
 * Domain interface for the ADR pattern.
 * Domain objects contain business logic and data.
 */
interface DomainInterface
{
    /**
     * Check if the domain has a specific capability.
     *
     * @param string $capability The capability to check
     * @return bool True if the domain has the capability
     */
    public function hasCapability(string $capability): bool;
}