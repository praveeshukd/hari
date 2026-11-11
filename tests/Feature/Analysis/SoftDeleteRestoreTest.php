<?php

namespace Tests\Feature\Analysis;

use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_soft_delete_and_restore(): void
    {
        $admin = User::factory()->admin()->create();
        $analysis = Analysis::factory()->create();

        $this->actingAs($admin)
            ->delete(route('analysis.destroy', $analysis))
            ->assertRedirect(route('analysis.index'));

        $this->assertSoftDeleted('analyses', ['id' => $analysis->id]);

        $this->actingAs($admin)
            ->post(route('analysis.restore', $analysis->id))
            ->assertRedirect(route('analysis.index', ['trash' => 1]));

        $this->assertDatabaseHas('analyses', [
            'id' => $analysis->id,
            'deleted_at' => null,
        ]);
    }
}
