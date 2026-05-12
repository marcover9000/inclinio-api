<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('vat', 32)->nullable()->index();
            $table->string('website', 255)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_client')->default(false);
            $table->timestamp('became_client_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['name', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
