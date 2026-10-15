<?php

namespace Tests\Unit\Domain;

use App\Domain\Visits\Enums\VisitStatus;
use App\Domain\Visits\Exceptions\InvalidVisitStatusTransitionException;
use App\Domain\Visits\Rules\VisitStatusTransitionRule;
use PHPUnit\Framework\TestCase;

class VisitStatusTransitionRuleTest extends TestCase
{
    private VisitStatusTransitionRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new VisitStatusTransitionRule;
    }

    public function test_initial_creation_allows_only_registered(): void
    {
        $this->assertTrue($this->rule->isAllowed(null, VisitStatus::REGISTERED));
        $this->assertFalse($this->rule->isAllowed(null, VisitStatus::ARRIVED));
        $this->assertFalse($this->rule->isAllowed(null, VisitStatus::IN_SERVICE));
        $this->assertFalse($this->rule->isAllowed(null, VisitStatus::COMPLETED));
        $this->assertFalse($this->rule->isAllowed(null, VisitStatus::CANCELLED));

        $this->expectException(InvalidVisitStatusTransitionException::class);
        $this->rule->validate(null, VisitStatus::ARRIVED);
    }

    public function test_registered_can_transition_to_arrived_or_cancelled(): void
    {
        $this->assertTrue($this->rule->isAllowed(VisitStatus::REGISTERED, VisitStatus::ARRIVED));
        $this->assertTrue($this->rule->isAllowed(VisitStatus::REGISTERED, VisitStatus::CANCELLED));

        $this->assertFalse($this->rule->isAllowed(VisitStatus::REGISTERED, VisitStatus::IN_SERVICE));
        $this->assertFalse($this->rule->isAllowed(VisitStatus::REGISTERED, VisitStatus::COMPLETED));
        $this->assertFalse($this->rule->isAllowed(VisitStatus::REGISTERED, VisitStatus::REGISTERED));
    }

    public function test_arrived_can_transition_to_in_service_or_cancelled(): void
    {
        $this->assertTrue($this->rule->isAllowed(VisitStatus::ARRIVED, VisitStatus::IN_SERVICE));
        $this->assertTrue($this->rule->isAllowed(VisitStatus::ARRIVED, VisitStatus::CANCELLED));

        $this->assertFalse($this->rule->isAllowed(VisitStatus::ARRIVED, VisitStatus::REGISTERED));
        $this->assertFalse($this->rule->isAllowed(VisitStatus::ARRIVED, VisitStatus::COMPLETED));
    }

    public function test_in_service_can_transition_to_completed_or_cancelled(): void
    {
        $this->assertTrue($this->rule->isAllowed(VisitStatus::IN_SERVICE, VisitStatus::COMPLETED));
        $this->assertTrue($this->rule->isAllowed(VisitStatus::IN_SERVICE, VisitStatus::CANCELLED));

        $this->assertFalse($this->rule->isAllowed(VisitStatus::IN_SERVICE, VisitStatus::REGISTERED));
        $this->assertFalse($this->rule->isAllowed(VisitStatus::IN_SERVICE, VisitStatus::ARRIVED));
    }

    public function test_completed_is_terminal_and_cannot_transition(): void
    {
        $this->assertTrue(VisitStatus::COMPLETED->isTerminal());

        foreach (VisitStatus::cases() as $status) {
            $this->assertFalse($this->rule->isAllowed(VisitStatus::COMPLETED, $status));
        }

        $this->expectException(InvalidVisitStatusTransitionException::class);
        $this->rule->validate(VisitStatus::COMPLETED, VisitStatus::IN_SERVICE);
    }

    public function test_cancelled_is_terminal_and_cannot_transition(): void
    {
        $this->assertTrue(VisitStatus::CANCELLED->isTerminal());

        foreach (VisitStatus::cases() as $status) {
            $this->assertFalse($this->rule->isAllowed(VisitStatus::CANCELLED, $status));
        }

        $this->expectException(InvalidVisitStatusTransitionException::class);
        $this->rule->validate(VisitStatus::CANCELLED, VisitStatus::REGISTERED);
    }

    public function test_exception_contains_correct_statuses(): void
    {
        try {
            $this->rule->validate(VisitStatus::REGISTERED, VisitStatus::COMPLETED);
            $this->fail('Harus melempar InvalidVisitStatusTransitionException');
        } catch (InvalidVisitStatusTransitionException $e) {
            $this->assertSame(VisitStatus::REGISTERED, $e->fromStatus);
            $this->assertSame(VisitStatus::COMPLETED, $e->toStatus);
            $this->assertStringContainsString('REGISTERED', $e->getMessage());
            $this->assertStringContainsString('COMPLETED', $e->getMessage());
        }
    }
}
