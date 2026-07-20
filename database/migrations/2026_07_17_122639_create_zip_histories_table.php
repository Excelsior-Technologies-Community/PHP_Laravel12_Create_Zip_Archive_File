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
        Schema::create('zip_histories', function (Blueprint $table) {
            $table->id();

            // Zip file name
            $table->string('zip_name');

            // Number of files inside zip
            $table->integer('total_files');

            // Zip size (KB/MB)
            $table->string('zip_size');

            // Zip creation type
            $table->enum('source', [
                'upload',
                'directory',
                'selected_files',
                'command'
            ])->default('upload');

            // Status
            $table->enum('status', [
                'success',
                'failed'
            ])->default('success');

            // Optional remarks
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zip_histories');
    }
};
