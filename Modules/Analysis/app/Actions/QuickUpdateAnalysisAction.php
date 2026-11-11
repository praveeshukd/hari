<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\DTOs\QuickUpdateData;
use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Services\NotifyAnalysisFailureService;
use Modules\Analysis\Support\PressureNormalizer;
use Modules\Analysis\Models\Analysis;
use InvalidArgumentException;

final class QuickUpdateAnalysisAction
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly PressureNormalizer $pressureNormalizer,
        private readonly RecordAnalysisActivityAction $activity,
        private readonly NotifyAnalysisFailureService $failureNotifier,
    ) {}

    public function execute(Analysis $analysis, QuickUpdateData $data): Analysis
    {
        if (! in_array($data->field, QuickUpdateData::ALLOWED_FIELDS, true)) {
            throw new InvalidArgumentException('Invalid field');
        }

        $value = $data->value;
        if ($data->field === 'pressure') {
            $value = $this->pressureNormalizer->normalize($value);
        }

        $before = $analysis->{$data->field};
        $analysis = $this->analyses->updateField($analysis, $data->field, $value, [
            'updated_by' => auth()->id(),
        ]);

        $this->activity->execute($analysis, 'quick_updated', auth()->user(), [
            'field' => $data->field,
            'before' => $before,
            'after' => $value,
        ]);
        $this->failureNotifier->maybeNotify($analysis);

        return $analysis;
    }
}
