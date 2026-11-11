<?php

namespace Tests\Feature\Analysis;

use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_an_analysis_record(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('analysis.store'), [
            'date' => '2026-11-10',
            'sample_name' => 'Paracetamol',
            'batch_no' => 'B1001',
            'analysis' => 'Assay by HPLC',
            'pressure' => '145 bar',
            'column_name' => 'C18',
            'instrument' => 'HPLC-01',
            'std_con' => 1,
            'std_area' => 1000,
            'sample_con' => 1,
            'sample_area' => 990,
            'result' => 'PASS',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $record = Analysis::query()->latest('id')->first();
        $this->assertNotNull($record);
        $this->assertSame('Paracetamol', $record->sample_name);
        $this->assertSame('B1001', $record->batch_no);
        $this->assertEquals(145, (float) $record->pressure);
    }

    public function test_store_validation_requires_core_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('analysis.store'), []);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'date',
            'sample_name',
            'batch_no',
            'analysis',
        ]);
    }

    public function test_quick_update_rejects_unknown_fields(): void
    {
        $user = User::factory()->create();
        $analysis = Analysis::factory()->create();

        $response = $this->actingAs($user)->patchJson(
            route('analysis.quick-update', $analysis),
            ['field' => 'not_a_column', 'value' => 'x']
        );

        $response->assertStatus(422);
    }

    public function test_quick_update_updates_an_allowed_field(): void
    {
        $user = User::factory()->create();
        $analysis = Analysis::factory()->create(['sample_name' => 'Old']);

        $response = $this->actingAs($user)->patchJson(
            route('analysis.quick-update', $analysis),
            ['field' => 'sample_name', 'value' => 'Updated Sample']
        );

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame('Updated Sample', $analysis->fresh()->sample_name);
    }
}
