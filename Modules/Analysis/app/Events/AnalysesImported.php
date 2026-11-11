<?php

namespace Modules\Analysis\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalysesImported
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $imported,
        public readonly int $skipped,
        public readonly array $errors,
    ) {}
}
