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
        Schema::table('zip_histories', function (Blueprint $table) {
            $table->boolean('is_encrypted')->default(false)->after('status');
            $table->string('encryption_method')->nullable()->after('is_encrypted');
            $table->string('compression_level')->default('normal')->after('encryption_method'); // 'store', 'normal', 'maximum'
            $table->unsignedBigInteger('original_size_bytes')->default(0)->after('compression_level');
            $table->unsignedBigInteger('compressed_size_bytes')->default(0)->after('original_size_bytes');
            $table->decimal('savings_percent', 5, 2)->default(0)->after('compressed_size_bytes');
            $table->string('checksum_sha256', 64)->nullable()->after('savings_percent');
        });

        Schema::create('zip_extracted_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zip_history_id')->nullable()->constrained('zip_histories')->onDelete('set null');
            $table->string('archive_name');
            $table->string('extracted_folder');
            $table->integer('total_files_extracted')->default(0);
            $table->unsignedBigInteger('total_size_bytes')->default(0);
            $table->string('status')->default('success');
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zip_extracted_histories');

        Schema::table('zip_histories', function (Blueprint $table) {
            $table->dropColumn([
                'is_encrypted',
                'encryption_method',
                'compression_level',
                'original_size_bytes',
                'compressed_size_bytes',
                'savings_percent',
                'checksum_sha256',
            ]);
        });
    }
};
