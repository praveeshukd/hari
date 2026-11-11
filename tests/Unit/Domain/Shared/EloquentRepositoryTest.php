<?php

namespace Tests\Unit\Domain\Shared;

use App\Domain\Analysis\Repositories\EloquentAnalysisRepository;
use App\Models\Analysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_base_soft_delete_find_and_restore_via_analysis_repository(): void
    {
        $repo = app(EloquentAnalysisRepository::class);
        $analysis = Analysis::factory()->create(['sample_name' => 'Soft Delete Me']);

        $repo->delete($analysis);

        $this->assertNull($repo->find($analysis->id));
        $this->assertNotNull($repo->findWithTrashed($analysis->id));
        $this->assertTrue($repo->findWithTrashed($analysis->id)->trashed());

        $repo->restore($analysis->fresh());

        $restored = $repo->find($analysis->id);
        $this->assertNotNull($restored);
        $this->assertFalse($restored->trashed());
        $this->assertSame('Soft Delete Me', $restored->sample_name);
    }

    public function test_create_from_data_persists_via_shared_create(): void
    {
        $repo = app(EloquentAnalysisRepository::class);

        $created = $repo->create([
            'date' => '2026-03-01',
            'sample_name' => 'Base Create',
            'batch_no' => 'B-BASE',
            'analysis' => 'Assay',
            'column_name' => 'C18',
            'instrument' => 'HPLC-01',
            'result' => 'PASS',
        ]);

        $this->assertInstanceOf(Analysis::class, $created);
        $this->assertSame('Base Create', $created->fresh()->sample_name);
        $this->assertSame($created->id, $repo->findOrFail($created->id)->id);
    }
}
