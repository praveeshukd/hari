<?php

namespace Tests\Feature\Analysis;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Analysis\Models\Analysis;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_store_and_delete_analysis(): void
    {
        $user = User::factory()->viewer()->create();
        $analysis = Analysis::factory()->create();

        $this->actingAs($user)->post(route('analysis.store'), [
            'date' => now()->toDateString(),
            'sample_name' => 'Sample A',
            'batch_no' => 'B1001',
            'analysis' => 'Assay',
            'column_name' => 'C18',
            'instrument' => 'HPLC-01',
            'result' => 'PASS',
        ])->assertRedirect(route('analysis.index'));

        $this->actingAs($user)
            ->delete(route('analysis.destroy', $analysis))
            ->assertRedirect();
    }

    public function test_users_management_routes_are_removed(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/users')
            ->assertNotFound();
    }
}
