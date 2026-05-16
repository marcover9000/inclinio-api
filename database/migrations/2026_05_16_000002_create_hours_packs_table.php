<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hours_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('hours');
            $table->integer('price_cents');
            $table->string('price_currency', 3);
            $table->date('dated_on');
            $table->string('reason', 200);
            $table->foreignId('source_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hours_packs');
    }
};
