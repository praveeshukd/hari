<?php

namespace Tests\Feature\Analysis;

use App\Domain\Analysis\Services\DashboardMetricsService;
use App\Models\Analysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_metrics_with_sql_aggregates(): void
    {
        Analysis::factory()->create([
            'date' => now()->toDateString(),
            'result' => 'PASS',
            'purity' => 99.1,
            'instrument' => 'HPLC-01',
        ]);
        Analysis::factory()->create([
            'date' => now()->toDateString(),
            'result' => 'FAIL',
            'purity' => 80.0,
            'instrument' => 'HPLC-02',
        ]);

        $overview = app(DashboardMetricsService::class)->build();

        $this->assertCount(5, $overview['cards']);
        $this->assertArrayHasKey('pass_rate', $overview['performance']);
        $this->assertCount(7, $overview['chart']['labels']);
        $this->assertCount(7, $overview['chart']['samples']);
        $this->assertGreaterThanOrEqual(1, $overview['performance']['samples']);
    }
}
