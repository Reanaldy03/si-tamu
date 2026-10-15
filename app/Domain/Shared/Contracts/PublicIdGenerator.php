<?php

namespace App\Domain\Shared\Contracts;

interface PublicIdGenerator
{
    /**
     * Generate a new unique 26-character public identifier.
     */
    public function generate(): string;
}
