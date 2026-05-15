<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->string('status', 20)->default('new')->index();
            $table->string('source', 20);
            $table->text('message')->nullable();
            $table->json('tags')->default(new \Illuminate\Database\Query\Expression('(JSON_ARRAY())'));
            $table->index('person_id');
            $table->index('company_id');
            $table->timestamp('status_changed_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['deleted_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
