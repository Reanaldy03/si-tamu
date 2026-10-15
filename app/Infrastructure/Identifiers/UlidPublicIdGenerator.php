<?php

namespace App\Infrastructure\Identifiers;

use App\Domain\Shared\Contracts\PublicIdGenerator;
use Illuminate\Support\Str;

class UlidPublicIdGenerator implements PublicIdGenerator
{
    /**
     * Generate a new 26-character ULID public identifier.
     */
    public function generate(): string
    {
        return (string) Str::ulid();
    }
}
