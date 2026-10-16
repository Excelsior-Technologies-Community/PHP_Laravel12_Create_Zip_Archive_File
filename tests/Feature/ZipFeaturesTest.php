<?php

namespace Tests\Feature;

use App\Models\ZipHistory;
use App\Models\ZipExtractedHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ZipFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists(storage_path('app/public/zips'))) {
            mkdir(storage_path('app/public/zips'), 0777, true);
        }
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }
        if (!file_exists(storage_path('app/extracted'))) {
            mkdir(storage_path('app/extracted'), 0777, true);
        }
    }

    public function test_zip_index_page_loads_successfully(): void
    {
        $response = $this->get(route('zip.index'));

        $response->assertStatus(200);
        $response->assertSee('ZIP Archive Manager');
        $response->assertSee('AES-256 Encryption');
        $response->assertSee('Online Extractor');
        $response->assertSee('Storage Insights');
    }

    public function test_create_zip_with_password_and_encryption(): void
    {
        $file1 = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('invoice.txt', 50, 'text/plain');

        $response = $this->post(route('zip.create.from.upload'), [
            'uploaded_files' => [$file1, $file2],
            'password' => 'SecurePass123!',
            'compression_level' => 'maximum',
        ]);

        $response->assertStatus(200);
        $response->assertDownload();

        $this->assertDatabaseHas('zip_histories', [
            'is_encrypted' => true,
            'encryption_method' => 'AES-256',
            'compression_level' => 'maximum',
            'total_files' => 2,
            'source' => 'upload',
            'status' => 'success',
        ]);

        $record = ZipHistory::first();
        $this->assertNotNull($record);
        $this->assertNotNull($record->checksum_sha256);
        $this->assertGreaterThan(0, $record->compressed_size_bytes);
        $this->assertFileExists($record->zip_path);

        if (file_exists($record->zip_path)) {
            @unlink($record->zip_path);
        }
    }

    public function test_create_zip_with_smart_exclusion_filters(): void
    {
        $file1 = UploadedFile::fake()->create('script.py', 50, 'text/x-python');
        $file2 = UploadedFile::fake()->create('debug.log', 20, 'text/plain');
        $file3 = UploadedFile::fake()->create('cache.tmp', 10, 'text/plain');

        $response = $this->post(route('zip.create.from.upload'), [
            'uploaded_files' => [$file1, $file2, $file3],
            'compression_level' => 'store',
            'exclude_ext' => 'log, tmp',
        ]);

        $response->assertStatus(200);
        $response->assertDownload();

        $this->assertDatabaseHas('zip_histories', [
            'total_files' => 1,
            'compression_level' => 'store',
            'is_encrypted' => false,
            'source' => 'upload',
        ]);

        $record = ZipHistory::first();
        if ($record && file_exists($record->zip_path)) {
            @unlink($record->zip_path);
        }
    }

    public function test_inspect_zip_archive_api(): void
    {
        $file = UploadedFile::fake()->create('test_document.txt', 25, 'text/plain');

        $this->post(route('zip.create.from.upload'), [
            'uploaded_files' => [$file],
        ]);

        $history = ZipHistory::first();
        $this->assertNotNull($history);

        $response = $this->get(route('zip.inspect', $history->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'archive_name',
            'total_files',
            'is_encrypted',
            'files' => [
                '*' => ['index', 'name', 'size_formatted', 'compressed_formatted', 'ratio', 'crc', 'is_dir']
            ]
        ]);
        $response->assertJson([
            'success' => true,
            'total_files' => 1,
        ]);

        if (file_exists($history->zip_path)) {
            @unlink($history->zip_path);
        }
    }

    public function test_extract_zip_archive_from_history(): void
    {
        $file = UploadedFile::fake()->create('unzip_me.txt', 15, 'text/plain');

        $this->post(route('zip.create.from.upload'), [
            'uploaded_files' => [$file],
        ]);

        $history = ZipHistory::first();
        $this->assertNotNull($history);

        $response = $this->post(route('zip.history.extract', $history->id), [
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('zip_extracted_histories', [
            'zip_history_id' => $history->id,
            'total_files_extracted' => 1,
            'status' => 'success',
        ]);

        if (file_exists($history->zip_path)) {
            @unlink($history->zip_path);
        }
    }

    public function test_download_single_file_from_zip(): void
    {
        $file = UploadedFile::fake()->create('solo_file.txt', 12, 'text/plain');

        $this->post(route('zip.create.from.upload'), [
            'uploaded_files' => [$file],
        ]);

        $history = ZipHistory::first();
        $this->assertNotNull($history);

        $response = $this->get(route('zip.download.file', [
            'id' => $history->id,
            'file' => 'solo_file.txt',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="solo_file.txt"');

        if (file_exists($history->zip_path)) {
            @unlink($history->zip_path);
        }
    }

    public function test_analytics_api_endpoint(): void
    {
        ZipHistory::create([
            'zip_name' => 'mock_archive.zip',
            'total_files' => 5,
            'zip_size' => '2.50 MB',
            'source' => 'upload',
            'status' => 'success',
            'zip_path' => 'zips/mock_archive.zip',
            'original_size_bytes' => 3145728,
            'compressed_size_bytes' => 2621440,
            'savings_percent' => 16.67,
            'compression_level' => 'normal',
            'is_encrypted' => true,
            'encryption_method' => 'AES-256',
        ]);

        $response = $this->get(route('zip.analytics'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'chart' => [
                'labels',
                'sizes_kb',
                'counts',
            ],
            'sources' => [
                'labels',
                'data',
            ],
            'encryption' => [
                'labels',
                'data',
            ],
            'savings' => [
                'total_original_bytes',
                'total_original_formatted',
                'total_compressed_bytes',
                'total_compressed_formatted',
                'total_saved_bytes',
                'total_saved_formatted',
                'savings_percent',
            ],
            'largest_archives'
        ]);
    }
}
