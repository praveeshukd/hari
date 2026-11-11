<?php

namespace Tests\Feature\Analysis;

use App\Jobs\ImportAnalysesJob;
use App\Models\Analysis;
use App\Models\User;
use App\Notifications\AnalysisFailedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FailAlertAndImportJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_fail_result_notifies_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $analyst = User::factory()->analyst()->create();

        $this->actingAs($analyst)->post(route('analysis.store'), [
            'date' => now()->toDateString(),
            'sample_name' => 'Failing Sample',
            'batch_no' => 'B9001',
            'analysis' => 'Assay',
            'column_name' => 'C18',
            'instrument' => 'HPLC-01',
            'result' => 'FAIL',
            'std_con' => 1,
            'std_area' => 1000,
            'sample_con' => 1,
            'sample_area' => 500,
        ])->assertRedirect(route('analysis.index'));

        Notification::assertSentTo($admin, AnalysisFailedNotification::class);
    }

    public function test_import_is_queued(): void
    {
        Queue::fake();
        Storage::fake('local');

        $user = User::factory()->analyst()->create();
        $file = UploadedFile::fake()->create('import.xlsx', 20, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)
            ->post(route('analysis.import.store'), ['file' => $file])
            ->assertRedirect(route('analysis.index'));

        Queue::assertPushed(ImportAnalysesJob::class);
    }

    public function test_bulk_fail_triggers_notification(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $row = Analysis::factory()->create(['result' => 'PASS']);

        $this->actingAs($admin)
            ->post(route('analysis.bulk-result'), [
                'ids' => [$row->id],
                'result' => 'FAIL',
            ])
            ->assertRedirect();

        Notification::assertSentTo($admin, AnalysisFailedNotification::class);
    }
}
