<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->index('date');
            $table->index('sample_name');
            $table->index('batch_no');
            $table->index('result');
            $table->index('instrument');
            $table->index(['date', 'result']);
        });
    }

    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropIndex(['analyses_date_index']);
            $table->dropIndex(['analyses_sample_name_index']);
            $table->dropIndex(['analyses_batch_no_index']);
            $table->dropIndex(['analyses_result_index']);
            $table->dropIndex(['analyses_instrument_index']);
            $table->dropIndex(['analyses_date_result_index']);
        });
    }
};
