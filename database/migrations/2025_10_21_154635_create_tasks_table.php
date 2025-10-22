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
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->boolean('is_completed')->default(false);
        $table->date('due_date')->nullable();
        
        // UBAH INI - gunakan 'lists' bukan 'task_lists'
        $table->foreignId('list_id')
              ->constrained('lists')  // ← PERBAIKAN INI
              ->onDelete('cascade');
              
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
