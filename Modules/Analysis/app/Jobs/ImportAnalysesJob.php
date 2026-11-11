<?php

namespace Modules\Analysis\Jobs;

use Modules\Analysis\Services\ImportAnalysesService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImportAnalysesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $storedPath,
        public readonly string $originalName,
        public readonly ?int $userId = null,
    ) {}

    public function handle(ImportAnalysesService $imports): void
    {
        $absolute = Storage::disk('local')->path($this->storedPath);
        $uploaded = new UploadedFile(
            $absolute,
            $this->originalName,
            null,
            null,
            true
        );

        $imports->execute($uploaded);

        Storage::disk('local')->delete($this->storedPath);
    }
}
