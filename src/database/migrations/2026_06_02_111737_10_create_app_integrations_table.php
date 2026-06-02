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
        Schema::create('app_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipe_app_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('provider_project_id')->nullable();
            $table->string('provider_api_key_id')->nullable();
            $table->string('provider_resource_id')->nullable();
            $table->string('label')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['pipe_app_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_integrations');
    }
};
