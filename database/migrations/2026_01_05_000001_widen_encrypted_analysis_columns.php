<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('analyses'))->pluck('name');

        Schema::table('analyses', function (Blueprint $table) use ($indexes) {
            // Encrypted ciphertext is longer than plaintext; drop old string indexes
            // (exact filters use *_hash columns instead).
            if ($indexes->contains('analyses_sample_name_index')) {
                $table->dropIndex('analyses_sample_name_index');
            }
            if ($indexes->contains('analyses_batch_no_index')) {
                $table->dropIndex('analyses_batch_no_index');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE analyses MODIFY sample_name TEXT NOT NULL');
            DB::statement('ALTER TABLE analyses MODIFY batch_no TEXT NOT NULL');
            DB::statement('ALTER TABLE analyses MODIFY done_by TEXT NULL');
            if (Schema::hasColumn('analyses', 'sample_submitted_by')) {
                DB::statement('ALTER TABLE analyses MODIFY sample_submitted_by TEXT NULL');
            }
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE analyses ALTER COLUMN sample_name TYPE TEXT');
            DB::statement('ALTER TABLE analyses ALTER COLUMN batch_no TYPE TEXT');
            DB::statement('ALTER TABLE analyses ALTER COLUMN done_by TYPE TEXT');
            if (Schema::hasColumn('analyses', 'sample_submitted_by')) {
                DB::statement('ALTER TABLE analyses ALTER COLUMN sample_submitted_by TYPE TEXT');
            }
        } else {
            // sqlite: recreate is unnecessary for local/dev; leave as-is
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE analyses MODIFY sample_name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE analyses MODIFY batch_no VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE analyses MODIFY done_by VARCHAR(255) NULL');
            if (Schema::hasColumn('analyses', 'sample_submitted_by')) {
                DB::statement('ALTER TABLE analyses MODIFY sample_submitted_by VARCHAR(255) NULL');
            }
        }

        Schema::table('analyses', function (Blueprint $table) {
            $table->index('sample_name');
            $table->index('batch_no');
        });
    }
};
