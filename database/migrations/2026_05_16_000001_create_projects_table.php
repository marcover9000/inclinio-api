<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('status', 20)->default('active')->index();
            $table->boolean('is_internal')->default(false);
            $table->foreignId('client_company_id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->foreignId('client_person_id')->nullable()->constrained('people')->restrictOnDelete();
            $table->integer('shadow_rate_override_cents')->nullable();
            $table->string('shadow_rate_override_currency', 3)->nullable();
            $table->date('started_at')->nullable();
            $table->date('due_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('client_company_id');
            $table->index('client_person_id');
            $table->index(['deleted_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
