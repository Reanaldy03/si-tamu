<?php

namespace Tests\Unit\Infrastructure;

use App\Domain\Shared\Contracts\PublicIdGenerator;
use App\Infrastructure\Identifiers\UlidPublicIdGenerator;
use Tests\TestCase;

class UlidPublicIdGeneratorTest extends TestCase
{
    private PublicIdGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = $this->app->make(PublicIdGenerator::class);
    }

    public function test_service_container_resolves_contract_to_ulid_generator(): void
    {
        $this->assertInstanceOf(UlidPublicIdGenerator::class, $this->generator);
    }

    public function test_generates_valid_26_character_crockford_base32_ulid(): void
    {
        $ulid = $this->generator->generate();

        $this->assertIsString($ulid);
        $this->assertSame(26, strlen($ulid));
        // Crockford Base32 characters (0-9, A-Z excluding I, L, O, U)
        $this->assertMatchesRegularExpression('/^[0123456789ABCDEFGHJKMNPQRSTVWXYZ]{26}$/', $ulid);
    }

    public function test_generates_unique_identifiers(): void
    {
        $ids = [];
        for ($i = 0; $i < 50; $i++) {
            $ids[] = $this->generator->generate();
        }

        $this->assertCount(50, array_unique($ids));
    }
}
