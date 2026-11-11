<?php

namespace Tests\Feature\Analysis;

use Modules\Analysis\Support\SensitiveValueHasher;
use Modules\Analysis\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EncryptionAndFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_fields_round_trip_encrypted_and_hash_filter_works(): void
    {
        $user = User::factory()->admin()->create();
        $analysis = Analysis::factory()->create([
            'sample_name' => 'Alpha Sample',
            'batch_no' => 'BATCH-77',
            'done_by' => 'Priya',
            'remarks' => 'confidential note',
            'sample_submitted_by' => 'Lab Desk',
        ]);

        $raw = DB::table('analyses')->where('id', $analysis->id)->first();
        $this->assertNotSame('Alpha Sample', $raw->sample_name);
        $this->assertNotNull($raw->sample_name_hash);
        $this->assertSame('Alpha Sample', $analysis->fresh()->sample_name);
        $this->assertSame('confidential note', $analysis->fresh()->remarks);

        $hasher = app(SensitiveValueHasher::class);
        $this->assertSame($hasher->hash('Alpha Sample'), $raw->sample_name_hash);

        $this->actingAs($user)
            ->getJson(route('analysis.index', ['filter_sample' => 'Alpha Sample']))
            ->assertOk();

        $matched = Analysis::query()
            ->filtered(['filter_sample' => 'Alpha Sample'], false)
            ->pluck('id')
            ->all();

        $this->assertContains($analysis->id, $matched);
    }
}
