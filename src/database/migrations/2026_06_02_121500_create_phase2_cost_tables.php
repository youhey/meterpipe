<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cost_providers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_enabled')->default(false);
            $table->json('settings')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cost_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('provider_key');
            $table->string('status');
            $table->string('scope')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('records_fetched')->default(0);
            $table->unsignedInteger('records_saved')->default(0);
            $table->string('error_class')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['provider_key', 'status']);
            $table->index(['provider_key', 'created_at']);
            $table->index(['period_start', 'period_end']);
        });

        Schema::create('cost_records', function (Blueprint $table) {
            $table->id();
            $table->string('provider_key');
            $table->string('source_record_key');
            $table->timestamp('bucket_start');
            $table->timestamp('bucket_end');
            $table->date('bucket_date');
            $table->decimal('amount', 20, 8);
            $table->string('currency', 8)->default('usd');
            $table->string('pipe_app_key')->nullable();
            $table->string('external_project_id')->nullable();
            $table->string('external_api_key_id')->nullable();
            $table->string('external_application_id')->nullable();
            $table->string('external_environment_id')->nullable();
            $table->string('line_item')->nullable();
            $table->string('resource_type')->nullable();
            $table->string('service_name')->nullable();
            $table->decimal('quantity', 20, 8)->nullable();
            $table->string('unit')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->unique(['provider_key', 'source_record_key'], 'cost_record_source_unique');
            $table->index(['provider_key', 'bucket_date']);
            $table->index(['pipe_app_key', 'bucket_date']);
            $table->index(['line_item', 'bucket_date']);
            $table->index(['resource_type', 'bucket_date']);
            $table->index(['external_project_id', 'bucket_date'], 'cost_record_project_idx');
            $table->index(['external_application_id', 'external_environment_id'], 'cost_record_cloud_env_idx');
        });

        Schema::create('cost_dimension_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('provider_key');
            $table->string('dimension_type');
            $table->string('external_id');
            $table->string('display_name')->nullable();
            $table->string('pipe_app_key')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['provider_key', 'dimension_type', 'external_id'], 'cost_dimension_unique');
        });

        Schema::create('cost_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('provider_key')->nullable();
            $table->string('pipe_app_key')->nullable();
            $table->string('period_type')->default('monthly');
            $table->decimal('amount', 20, 8);
            $table->string('currency', 8)->default('usd');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_budgets');
        Schema::dropIfExists('cost_dimension_mappings');
        Schema::dropIfExists('cost_records');
        Schema::dropIfExists('cost_sync_runs');
        Schema::dropIfExists('cost_providers');
    }
};
