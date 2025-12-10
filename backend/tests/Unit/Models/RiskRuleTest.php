<?php

namespace Tests\Unit\Models;

use App\Models\ConfiguredAction;
use App\Models\Incident;
use App\Models\RiskRule;
use Tests\TestCase;

class RiskRuleTest extends TestCase
{
    /**
     * Test that isHard returns true for HARD severity rules.
     */
    public function test_identifies_hard_rules(): void
    {
        $hardRule = RiskRule::factory()->hard()->create();
        $softRule = RiskRule::factory()->soft()->create();

        $this->assertTrue($hardRule->isHard());
        $this->assertFalse($softRule->isHard());
    }

    /**
     * Test that isSoft returns true for SOFT severity rules.
     */
    public function test_identifies_soft_rules(): void
    {
        $softRule = RiskRule::factory()->soft()->create();
        $hardRule = RiskRule::factory()->hard()->create();

        $this->assertTrue($softRule->isSoft());
        $this->assertFalse($hardRule->isSoft());
    }

    /**
     * Test that isActive returns correct value.
     */
    public function test_identifies_active_rules(): void
    {
        $activeRule = RiskRule::factory()->create(['is_active' => true]);
        $inactiveRule = RiskRule::factory()->inactive()->create();

        $this->assertTrue($activeRule->isActive());
        $this->assertFalse($inactiveRule->isActive());
    }

    /**
     * Test that rule has many actions through many-to-many relationship.
     */
    public function test_has_many_actions(): void
    {
        $rule = RiskRule::factory()->create();
        $action1 = ConfiguredAction::factory()->email()->create();
        $action2 = ConfiguredAction::factory()->slack()->create();

        $rule->actions()->attach([$action1->id, $action2->id]);

        $this->assertCount(2, $rule->actions);
        $this->assertInstanceOf(ConfiguredAction::class, $rule->actions->first());
    }

    /**
     * Test that parameters are cast to array.
     */
    public function test_casts_parameters_to_array(): void
    {
        $rule = RiskRule::factory()->minDuration(30)->create();

        $this->assertIsArray($rule->parameters);
        $this->assertEquals(30, $rule->parameters['min_duration_seconds']);
    }

    /**
     * Test that getParameter returns value with default fallback.
     */
    public function test_get_parameter_with_default(): void
    {
        $rule = RiskRule::factory()->create(['parameters' => ['foo' => 'bar']]);

        $this->assertEquals('bar', $rule->getParameter('foo'));
        $this->assertEquals('default', $rule->getParameter('missing', 'default'));
        $this->assertNull($rule->getParameter('missing'));
    }

    /**
     * Test that is_active is cast to boolean.
     */
    public function test_is_active_cast_to_boolean(): void
    {
        $rule = RiskRule::factory()->create(['is_active' => 1]);

        $this->assertTrue($rule->is_active);
        $this->assertIsBool($rule->is_active);
    }

    /**
     * Test that incident_limit is cast to integer.
     */
    public function test_incident_limit_cast_to_integer(): void
    {
        $rule = RiskRule::factory()->create(['incident_limit' => '5']);

        $this->assertEquals(5, $rule->incident_limit);
        $this->assertIsInt($rule->incident_limit);
    }

    /**
     * Test that rule has many incidents.
     */
    public function test_has_many_incidents(): void
    {
        $rule = RiskRule::factory()->create();
        Incident::factory()->forRule($rule)->count(3)->create();

        $this->assertCount(3, $rule->incidents);
        $this->assertInstanceOf(Incident::class, $rule->incidents->first());
    }
}
