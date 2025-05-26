<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('total_tasks_completed')->default(0);
            $table->integer('total_points')->default(0);
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->decimal('avg_completion_time', 8, 2)->nullable(); // En heures
            $table->decimal('fastest_completion_time', 8, 2)->nullable(); // En heures
            $table->integer('current_streak')->default(0); // Jours consécutifs
            $table->integer('longest_streak')->default(0);
            $table->datetime('last_task_date')->nullable();
            $table->integer('level')->default(1);
            $table->integer('experience_points')->default(0);
            $table->integer('monthly_tasks_completed')->default(0);
            $table->integer('weekly_tasks_completed')->default(0);
            $table->integer('monthly_points')->default(0);
            $table->integer('weekly_points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
