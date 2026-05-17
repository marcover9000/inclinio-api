<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('worked_on');
            $table->unsignedInteger('minutes');
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();
            $table->index('worked_on');
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
