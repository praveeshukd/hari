<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->decimal('standard_purity', 8, 4)->nullable()->after('sample_area');
            $table->string('request_form_no')->nullable()->after('batch_no');
        });
    }

    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropColumn(['standard_purity', 'request_form_no']);
        });
    }
};
