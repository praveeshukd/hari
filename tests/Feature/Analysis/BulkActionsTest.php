<?php

namespace Tests\Feature\Analysis;

use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyst_can_bulk_update_result_but_not_bulk_delete(): void
    {
        $analyst = User::factory()->analyst()->create();
        $rows = Analysis::factory()->count(2)->create(['result' => 'PASS']);

        $this->actingAs($analyst)
            ->post(route('analysis.bulk-result'), [
                'ids' => $rows->pluck('id')->all(),
                'result' => 'FAIL',
            ])
            ->assertRedirect();

        foreach ($rows as $row) {
            $this->assertSame('FAIL', $row->fresh()->result);
        }

        $this->actingAs($analyst)
            ->post(route('analysis.bulk-delete'), [
                'ids' => $rows->pluck('id')->all(),
            ])
            ->assertForbidden();
    }

    public function test_admin_can_bulk_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $rows = Analysis::factory()->count(2)->create();

        $this->actingAs($admin)
            ->post(route('analysis.bulk-delete'), [
                'ids' => $rows->pluck('id')->all(),
            ])
            ->assertRedirect();

        foreach ($rows as $row) {
            $this->assertSoftDeleted('analyses', ['id' => $row->id]);
        }
    }
}
