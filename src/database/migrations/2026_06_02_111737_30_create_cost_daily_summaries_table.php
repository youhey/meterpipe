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
            $table->string('source');
            $table->foreignId('pipe_app_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service')->nullable();
            $table->decimal('amount', 20, 8);
            $table->string('currency')->default('usd');
            $table->json('dimensions')->nullable();
            $table->string('dimensions_hash', 64);
            $table->date('date')->index();
            $table->timestamps();

            $table->unique(['source', 'pipe_app_id', 'service', 'date', 'dimensions_hash'], 'cost_daily_unique');
            $table->index(['source', 'date']);
            $table->index(['pipe_app_id', 'date']);
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
