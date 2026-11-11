<?php

namespace Tests\Feature\Analysis;

use App\Domain\Analysis\Services\DashboardMetricsService;
use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_respect_custom_date_range(): void
    {
        Analysis::factory()->create(['date' => '2026-01-10', 'result' => 'PASS']);
        Analysis::factory()->create(['date' => '2026-01-20', 'result' => 'PASS']);
        Analysis::factory()->create(['date' => '2026-02-01', 'result' => 'FAIL']);

        $metrics = app(DashboardMetricsService::class)->build('2026-01-01', '2026-01-31');

        $this->assertSame('2026-01-01', $metrics['from']);
        $this->assertSame('2026-01-31', $metrics['to']);
        $this->assertSame(2, $metrics['performance']['samples']);
        $this->assertCount(31, $metrics['chart']['labels']);
    }

    public function test_dashboard_page_accepts_from_to_query(): void
    {
        $user = User::factory()->analyst()->create();

        $this->actingAs($user)
            ->get(route('dashboard', ['from' => '2026-01-01', 'to' => '2026-01-07']))
            ->assertOk()
            ->assertSee('2026-01-01')
            ->assertSee('2026-01-07');
    }
}
