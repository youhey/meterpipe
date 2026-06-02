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
        Schema::create('metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->foreignId('pipe_app_id')->nullable()->constrained()->nullOnDelete();
            $table->string('metric_name');
            $table->decimal('value', 20, 8);
            $table->string('unit');
            $table->json('dimensions')->nullable();
            $table->timestamp('measured_at')->index();
            $table->timestamps();

            $table->index(['source', 'metric_name', 'measured_at']);
            $table->index(['pipe_app_id', 'metric_name', 'measured_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_snapshots');
    }
};
