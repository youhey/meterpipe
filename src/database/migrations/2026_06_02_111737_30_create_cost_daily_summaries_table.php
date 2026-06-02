<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cost_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date');
            $table->string('provider_key');
            $table->string('pipe_app_key')->nullable();
            $table->string('dimension_type')->nullable();
            $table->string('dimension_key')->nullable();
            $table->string('dimension_label')->nullable();
            $table->decimal('amount', 20, 8);
            $table->string('currency')->default('usd');
            $table->unsignedInteger('record_count')->default(0);
            $table->timestamp('calculated_at');
            $table->string('summary_key', 64);
            $table->timestamps();

            $table->unique('summary_key', 'cost_daily_unique');
            $table->index(['provider_key', 'summary_date']);
            $table->index(['pipe_app_key', 'summary_date']);
            $table->index(['dimension_type', 'dimension_key', 'summary_date'], 'cost_daily_dimension_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_daily_summaries');
    }
};
