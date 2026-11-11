<?php

namespace Tests\Feature\Analysis;

use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_cannot_store_analysis(): void
    {
        $viewer = User::factory()->viewer()->create();

        $response = $this->actingAs($viewer)->post(route('analysis.store'), [
            'date' => now()->toDateString(),
            'sample_name' => 'Sample A',
            'batch_no' => 'B1001',
            'analysis' => 'Assay',
            'column_name' => 'C18',
            'instrument' => 'HPLC-01',
            'result' => 'PASS',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('analyses', 0);
    }

    public function test_analyst_can_store_but_not_delete(): void
    {
        $analyst = User::factory()->analyst()->create();
        $analysis = Analysis::factory()->create();

        $this->actingAs($analyst)->post(route('analysis.store'), [
            'date' => now()->toDateString(),
            'sample_name' => 'Sample B',
            'batch_no' => 'B1002',
            'analysis' => 'Assay',
            'column_name' => 'C18',
            'instrument' => 'HPLC-01',
            'result' => 'PASS',
        ])->assertRedirect(route('analysis.index'));

        $this->actingAs($analyst)
            ->delete(route('analysis.destroy', $analysis))
            ->assertForbidden();
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = User::factory()->admin()->create();
        $analyst = User::factory()->analyst()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->patch(route('users.update-role', $analyst), ['role' => User::ROLE_VIEWER])
            ->assertRedirect();

        $this->assertSame(User::ROLE_VIEWER, $analyst->fresh()->role);
    }

    public function test_non_admin_cannot_open_users_page(): void
    {
        $analyst = User::factory()->analyst()->create();

        $this->actingAs($analyst)
            ->get(route('users.index'))
            ->assertForbidden();
    }
}
