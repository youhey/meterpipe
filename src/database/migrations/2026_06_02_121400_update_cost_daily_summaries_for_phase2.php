<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('cost_daily_summaries', 'summary_date')) {
            return;
        }

        Schema::table('cost_daily_summaries', function (Blueprint $table) {
            $table->date('summary_date')->nullable()->after('id');
            $table->string('provider_key')->nullable()->after('summary_date');
            $table->string('pipe_app_key')->nullable()->after('provider_key');
            $table->string('dimension_type')->nullable()->after('pipe_app_key');
            $table->string('dimension_key')->nullable()->after('dimension_type');
            $table->string('dimension_label')->nullable()->after('dimension_key');
            $table->unsignedInteger('record_count')->default(1)->after('currency');
            $table->timestamp('calculated_at')->nullable()->after('record_count');
            $table->string('summary_key', 64)->nullable()->after('calculated_at');
        });

        DB::table('cost_daily_summaries')
            ->orderBy('id')
            ->cursor()
            ->each(function (object $row): void {
                $summaryDate = (string) ($row->date ?? now()->toDateString());
                $providerKey = (string) ($row->source ?? 'all');
                $dimensionKey = $row->service !== null ? (string) $row->service : null;
                $summaryKey = hash('sha256', implode('|', [
                    $summaryDate,
                    $providerKey,
                    $row->pipe_app_id !== null ? (string) $row->pipe_app_id : '_',
                    $dimensionKey ?? '_',
                    (string) ($row->currency ?? 'usd'),
                    (string) ($row->dimensions_hash ?? $row->id),
                ]));

                DB::table('cost_daily_summaries')
                    ->where('id', $row->id)
                    ->update([
                        'summary_date' => $summaryDate,
                        'provider_key' => $providerKey,
                        'pipe_app_key' => null,
                        'dimension_type' => $dimensionKey !== null ? 'legacy_service' : null,
                        'dimension_key' => $dimensionKey,
                        'dimension_label' => $dimensionKey,
                        'record_count' => 1,
                        'calculated_at' => now(),
                        'summary_key' => $summaryKey,
                    ]);
            });

        Schema::table('cost_daily_summaries', function (Blueprint $table) {
            $table->dropForeign(['pipe_app_id']);
            $table->dropUnique('cost_daily_unique');
            $table->dropIndex(['source', 'date']);
            $table->dropIndex(['pipe_app_id', 'date']);
            $table->unique('summary_key', 'cost_daily_phase2_summary_unique');
            $table->index(['provider_key', 'summary_date'], 'cost_daily_phase2_provider_idx');
            $table->index(['pipe_app_key', 'summary_date'], 'cost_daily_phase2_app_idx');
            $table->index(['dimension_type', 'dimension_key', 'summary_date'], 'cost_daily_phase2_dimension_idx');
            $table->dropColumn([
                'source',
                'pipe_app_id',
                'service',
                'dimensions',
                'dimensions_hash',
                'date',
            ]);
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasColumn('cost_daily_summaries', 'summary_date')
            || ! Schema::hasColumn('cost_daily_summaries', 'source')
        ) {
            return;
        }

        Schema::table('cost_daily_summaries', function (Blueprint $table) {
            $table->dropIndex('cost_daily_phase2_provider_idx');
            $table->dropIndex('cost_daily_phase2_app_idx');
            $table->dropIndex('cost_daily_phase2_dimension_idx');
            $table->dropUnique('cost_daily_phase2_summary_unique');
            $table->dropColumn([
                'summary_date',
                'provider_key',
                'pipe_app_key',
                'dimension_type',
                'dimension_key',
                'dimension_label',
                'record_count',
                'calculated_at',
                'summary_key',
            ]);
        });
    }
};
