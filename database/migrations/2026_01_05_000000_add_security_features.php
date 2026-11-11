<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 32)->default('analyst')->after('password');
                $table->index('role');
            });
        }

        // Promote the earliest user to admin if any exist.
        $firstUserId = DB::table('users')->orderBy('id')->value('id');
        if ($firstUserId) {
            DB::table('users')->where('id', $firstUserId)->update(['role' => 'admin']);
        }

        Schema::table('analyses', function (Blueprint $table) {
            if (! Schema::hasColumn('analyses', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('analyses', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('analyses', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('analyses', 'sample_name_hash')) {
                $table->string('sample_name_hash', 64)->nullable()->after('sample_name');
                $table->index('sample_name_hash');
            }
            if (! Schema::hasColumn('analyses', 'batch_no_hash')) {
                $table->string('batch_no_hash', 64)->nullable()->after('batch_no');
                $table->index('batch_no_hash');
            }
            if (! Schema::hasColumn('analyses', 'done_by_hash')) {
                $table->string('done_by_hash', 64)->nullable()->after('done_by');
                $table->index('done_by_hash');
            }
        });

        if (! Schema::hasTable('analysis_activity_logs')) {
            Schema::create('analysis_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('analysis_id')->nullable()->constrained('analyses')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 64);
                $table->json('changes')->nullable();
                $table->timestamps();
                $table->index(['analysis_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('analysis_activity_logs');

        Schema::table('analyses', function (Blueprint $table) {
            if (Schema::hasColumn('analyses', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('analyses', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('analyses', 'sample_name_hash')) {
                $table->dropIndex(['sample_name_hash']);
                $table->dropColumn('sample_name_hash');
            }
            if (Schema::hasColumn('analyses', 'batch_no_hash')) {
                $table->dropIndex(['batch_no_hash']);
                $table->dropColumn('batch_no_hash');
            }
            if (Schema::hasColumn('analyses', 'done_by_hash')) {
                $table->dropIndex(['done_by_hash']);
                $table->dropColumn('done_by_hash');
            }
            if (Schema::hasColumn('analyses', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['role']);
                $table->dropColumn('role');
            });
        }
    }
};
