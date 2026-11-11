<?php

namespace Modules\Analysis\Console\Commands;

use Modules\Analysis\Models\Analysis;
use Illuminate\Console\Command;

class EncryptExistingAnalysesCommand extends Command
{
    protected $signature = 'analysis:encrypt-existing';

    protected $description = 'Re-save analyses to apply encrypted casts and hash columns';

    public function handle(): int
    {
        $fields = [
            'sample_name',
            'batch_no',
            'remarks',
            'sample_submitted_by',
            'done_by',
        ];

        $count = 0;
        Analysis::withTrashed()->orderBy('id')->chunkById(100, function ($rows) use (&$count, $fields) {
            foreach ($rows as $row) {
                // Force dirty so set() encrypts legacy plaintext (and refreshes hashes).
                foreach ($fields as $field) {
                    $row->{$field} = $row->{$field};
                }
                $row->save();
                $count++;
            }
        });

        $this->info("Re-saved {$count} analysis rows.");

        return self::SUCCESS;
    }
}
