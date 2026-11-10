<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('sample_name');
            $table->string('batch_no');
            $table->string('analysis');
            $table->decimal('pressure', 10, 4)->nullable();
            $table->string('column_name');
            $table->string('instrument');
            $table->decimal('std_con', 15, 6)->nullable();
            $table->decimal('std_area', 15, 4)->nullable();
            $table->decimal('sample_con', 15, 6)->nullable();
            $table->decimal('sample_area', 15, 4)->nullable();
            $table->decimal('purity', 8, 4)->nullable()->comment('Auto-calculated: (sample_area/std_area)*(std_con/sample_con)*100');
            $table->string('result')->nullable();
            $table->string('done_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
