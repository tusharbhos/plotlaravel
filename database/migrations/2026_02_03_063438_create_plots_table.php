<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->string('plot_id')->unique(); // e.g. "Plot 9", "Plot 419"
            $table->double('area')->default(0);
            $table->double('fsi')->default(1.1);
            $table->double('permissible_area')->default(0);
            $table->string('rl')->nullable();
            $table->string('status')->default('available'); // available, sold, under_review, booked
            $table->string('road')->nullable(); // e.g. "18MTR", "15 MTR"
            $table->string('plot_type')->default('Land parcel');
            $table->string('category')->default('PREMIUM'); // PREMIUM, STANDARD, ECO
            $table->boolean('corner')->default(false);
            $table->boolean('garden')->default(false);
            $table->text('notes')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes(); // Trash (soft delete)
            
            // Add indexes for better performance
            $table->index('status');
            $table->index('category');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};