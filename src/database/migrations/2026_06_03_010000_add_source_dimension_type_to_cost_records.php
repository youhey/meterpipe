<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cost_records', function (Blueprint $table): void {
            $table->string('source_dimension_type')->nullable()->after('pipe_app_key');
            $table->index(['provider_key', 'source_dimension_type', 'bucket_date'], 'cost_record_source_dimension_idx');
        });

        DB::table('cost_records')
            ->where('provider_key', 'openai')
            ->whereNull('external_project_id')
            ->whereNull('external_api_key_id')
            ->whereNull('line_item')
            ->update(['source_dimension_type' => 'total']);

        DB::table('cost_records')
            ->where('provider_key', 'openai')
            ->whereNotNull('external_project_id')
            ->whereNull('external_api_key_id')
            ->whereNull('line_item')
            ->update(['source_dimension_type' => 'project']);

        DB::table('cost_records')
            ->where('provider_key', 'openai')
            ->whereNotNull('external_api_key_id')
            ->whereNull('line_item')
            ->update(['source_dimension_type' => 'api_key']);

        DB::table('cost_records')
            ->where('provider_key', 'openai')
            ->whereNotNull('line_item')
            ->update(['source_dimension_type' => 'line_item']);

        DB::table('cost_records')
            ->where('provider_key', 'laravel_cloud')
            ->where('service_name', 'organization')
            ->update(['source_dimension_type' => 'total']);

        DB::table('cost_records')
            ->where('provider_key', 'laravel_cloud')
            ->whereNotNull('external_application_id')
            ->whereNull('external_environment_id')
            ->update(['source_dimension_type' => 'application']);

        DB::table('cost_records')
            ->where('provider_key', 'laravel_cloud')
            ->whereNotNull('external_environment_id')
            ->update(['source_dimension_type' => 'environment']);

        DB::table('cost_records')
            ->where('provider_key', 'laravel_cloud')
            ->whereNull('source_dimension_type')
            ->where('resource_type', 'add_on')
            ->update(['source_dimension_type' => 'add_on']);

        DB::table('cost_records')
            ->where('provider_key', 'laravel_cloud')
            ->whereNull('source_dimension_type')
            ->whereNotNull('resource_type')
            ->update(['source_dimension_type' => 'resource']);
    }

    public function down(): void
    {
        Schema::table('cost_records', function (Blueprint $table): void {
            $table->dropIndex('cost_record_source_dimension_idx');
            $table->dropColumn('source_dimension_type');
        });
    }
};
