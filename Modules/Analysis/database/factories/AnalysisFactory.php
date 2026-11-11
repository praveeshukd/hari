<?php

namespace Modules\Analysis\Database\Factories;

use Modules\Analysis\Models\Analysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Analysis>
 */
class AnalysisFactory extends Factory
{
    protected $model = Analysis::class;

    public function definition(): array
    {
        $stdArea = fake()->randomFloat(2, 1000, 5000);
        $sampleArea = fake()->randomFloat(2, 900, 5100);
        $stdCon = fake()->randomFloat(4, 0.5, 2);
        $sampleCon = fake()->randomFloat(4, 0.5, 2);

        return [
            'date' => fake()->dateTimeBetween('-14 days', 'now')->format('Y-m-d'),
            'sample_name' => fake()->words(2, true),
            'batch_no' => 'B'.fake()->numerify('####'),
            'request_form_no' => null,
            'analysis' => 'Assay by HPLC',
            'pressure' => fake()->randomFloat(2, 100, 200),
            'column_name' => 'C18 250x4.6mm',
            'instrument' => 'HPLC-0'.fake()->numberBetween(1, 3),
            'std_con' => $stdCon,
            'std_area' => $stdArea,
            'sample_con' => $sampleCon,
            'sample_area' => $sampleArea,
            'standard_purity' => 100.0,
            'result' => fake()->randomElement(['PASS', 'FAIL', 'PASS']),
            'done_by' => fake()->firstName(),
            'remarks' => null,
            'specification' => '0.80%',
            'sample_submitted_by' => 'Ann',
        ];
    }
}
