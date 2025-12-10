<?php

namespace Tests\Feature\Api;

use App\Models\ConfiguredAction;
use App\Models\RiskRule;
use Tests\TestCase;

class RiskRuleControllerTest extends TestCase
{
    /**
     * Test listing all risk rules.
     */
    public function test_can_list_all_risk_rules(): void
    {
        RiskRule::factory()->count(3)->create();

        $response = $this->getJson('/api/risk-rules');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'rule_type', 'severity', 'is_active'],
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test creating a new risk rule.
     */
    public function test_can_create_risk_rule(): void
    {
        $ruleData = [
            'name' => 'Test Rule',
            'description' => 'Test description',
            'rule_type' => 'min_duration',
            'parameters' => ['min_duration_seconds' => 30],
            'severity' => 'HARD',
            'incident_limit' => 3,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/risk-rules', $ruleData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'rule_type', 'severity'],
            ])
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Test Rule'],
            ]);

        $this->assertDatabaseHas('risk_rules', ['name' => 'Test Rule']);
    }

    /**
     * Test validation on rule creation.
     */
    public function test_validates_required_fields_on_create(): void
    {
        $response = $this->postJson('/api/risk-rules', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ])
            ->assertJsonPath('error.details.name', fn ($value) => !empty($value))
            ->assertJsonPath('error.details.rule_type', fn ($value) => !empty($value))
            ->assertJsonPath('error.details.severity', fn ($value) => !empty($value));
    }

    /**
     * Test validation of rule_type.
     */
    public function test_validates_rule_type(): void
    {
        $ruleData = [
            'name' => 'Test Rule',
            'rule_type' => 'invalid_type',
            'severity' => 'HARD',
        ];

        $response = $this->postJson('/api/risk-rules', $ruleData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ])
            ->assertJsonPath('error.details.rule_type', fn ($value) => !empty($value));
    }

    /**
     * Test showing a single rule.
     */
    public function test_can_show_single_rule(): void
    {
        $rule = RiskRule::factory()->create(['name' => 'Specific Rule']);

        $response = $this->getJson("/api/risk-rules/{$rule->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['id' => $rule->id, 'name' => 'Specific Rule'],
            ]);
    }

    /**
     * Test updating a rule.
     */
    public function test_can_update_rule(): void
    {
        $rule = RiskRule::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/risk-rules/{$rule->id}", [
            'name' => 'New Name',
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'New Name', 'is_active' => false],
            ]);

        $this->assertDatabaseHas('risk_rules', [
            'id' => $rule->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test deleting a rule.
     */
    public function test_can_delete_rule(): void
    {
        $rule = RiskRule::factory()->create();

        $response = $this->deleteJson("/api/risk-rules/{$rule->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('risk_rules', ['id' => $rule->id]);
    }

    /**
     * Test attaching actions to a rule.
     */
    public function test_can_attach_actions_to_rule(): void
    {
        $rule = RiskRule::factory()->create();
        $action1 = ConfiguredAction::factory()->email()->create();
        $action2 = ConfiguredAction::factory()->slack()->create();

        $response = $this->postJson("/api/risk-rules/{$rule->id}/actions", [
            'action_ids' => [$action1->id, $action2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $rule->refresh();
        $this->assertCount(2, $rule->actions);
    }

    /**
     * Test listing available actions.
     */
    public function test_can_list_available_actions(): void
    {
        ConfiguredAction::factory()->count(4)->create();

        $response = $this->getJson('/api/risk-rules/actions');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertCount(4, $response->json('data'));
    }

    /**
     * Test 404 for non-existent rule.
     */
    public function test_returns_404_for_nonexistent_rule(): void
    {
        $response = $this->getJson('/api/risk-rules/99999');

        $response->assertStatus(404);
    }
}
