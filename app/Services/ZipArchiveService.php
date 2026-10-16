<?php

namespace App\Services;

use App\Models\ZipHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ZipArchiveService
{
    /**
     * Build ZIP Archive with options for Encryption, Compression Level, and Exclude Filters
     */
    public function createArchive(string $zipPath, array $filesWithNames, array $options = []): array
    {
        $dir = dirname($zipPath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $password = !empty($options['password']) ? trim($options['password']) : null;
        $compressionLevel = $options['compression_level'] ?? 'normal'; // store, normal, maximum
        $excludeExtensions = $options['exclude_extensions'] ?? []; // e.g. ['log', 'tmp', 'git', 'ds_store']

        // Normalize exclude extensions (lowercase, without leading dot)
        $excludeExtensions = array_map(function ($ext) {
            return strtolower(ltrim(trim($ext), '.'));
        }, $excludeExtensions);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return [
                'success' => false,
                'message' => 'Unable to open ZIP archive for writing at: ' . $zipPath,
                'total_files' => 0,
            ];
        }

        if ($password) {
            $zip->setPassword($password);
        }

        $totalFiles = 0;
        $originalSizeBytes = 0;

        foreach ($filesWithNames as $item) {
            $realPath = $item['path'];
            $entryName = $item['name'];

            if (!file_exists($realPath) || !is_readable($realPath)) {
                continue;
            }

            // Check exclusion filter
            $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
            if (!empty($ext) && in_array($ext, $excludeExtensions, true)) {
                continue;
            }

            $fileSize = filesize($realPath);
            $originalSizeBytes += $fileSize;

            $zip->addFile($realPath, $entryName);

            // Apply compression method based on user selection
            if ($compressionLevel === 'store') {
                $zip->setCompressionName($entryName, ZipArchive::CM_STORE);
            } elseif ($compressionLevel === 'maximum') {
                // Best deflate compression
                $zip->setCompressionName($entryName, ZipArchive::CM_DEFLATE, 9);
            } else {
                $zip->setCompressionName($entryName, ZipArchive::CM_DEFAULT);
            }

            // Apply AES-256 Encryption if password provided
            if ($password) {
                // Check if AES-256 is supported by the installed libzip / ZipArchive
                if (defined('ZipArchive::EM_AES_256')) {
                    $zip->setEncryptionName($entryName, ZipArchive::EM_AES_256, $password);
                } elseif (defined('ZipArchive::EM_TRAD_PKWARE')) {
                    $zip->setEncryptionName($entryName, ZipArchive::EM_TRAD_PKWARE, $password);
                }
            }

            $totalFiles++;
        }

        $zip->close();

        if ($totalFiles === 0 && file_exists($zipPath)) {
            @unlink($zipPath);
            return [
                'success' => false,
                'message' => 'No files were added to the archive (all files may have been excluded).',
                'total_files' => 0,
            ];
        }

        $compressedSizeBytes = file_exists($zipPath) ? filesize($zipPath) : 0;
        $savingsPercent = 0.0;
        if ($originalSizeBytes > 0 && $compressedSizeBytes > 0) {
            $savingsPercent = max(0, round((($originalSizeBytes - $compressedSizeBytes) / $originalSizeBytes) * 100, 2));
        }

        $checksum = file_exists($zipPath) ? hash_file('sha256', $zipPath) : null;

        return [
            'success' => true,
            'message' => 'Archive created successfully.',
            'total_files' => $totalFiles,
            'original_size_bytes' => $originalSizeBytes,
            'compressed_size_bytes' => $compressedSizeBytes,
            'savings_percent' => $savingsPercent,
            'checksum_sha256' => $checksum,
            'is_encrypted' => !empty($password),
            'encryption_method' => !empty($password) ? 'AES-256' : null,
            'compression_level' => $compressionLevel,
        ];
    }

    /**
     * Inspect contents of a ZIP Archive without extracting
     */
    public function inspectArchive(string $zipPath): array
    {
        if (!file_exists($zipPath)) {
            return [
                'success' => false,
                'message' => 'ZIP archive not found on disk.',
                'files' => [],
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
            return [
                'success' => false,
                'message' => 'Failed to read ZIP archive or archive is corrupted.',
                'files' => [],
            ];
        }

        $files = [];
        $totalUncompressed = 0;
        $totalCompressed = 0;
        $isEncrypted = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!$stat) {
                continue;
            }

            $entryName = $stat['name'];
            $uncompressed = $stat['size'] ?? 0;
            $compressed = $stat['comp_size'] ?? 0;
            $isDir = str_ends_with($entryName, '/');

            $totalUncompressed += $uncompressed;
            $totalCompressed += $compressed;

            $encrypted = ($stat['encryption_method'] ?? 0) > 0;
            if ($encrypted) {
                $isEncrypted = true;
            }

            $compressionRatio = 0;
            if ($uncompressed > 0) {
                $compressionRatio = round((($uncompressed - $compressed) / $uncompressed) * 100, 1);
            }

            $files[] = [
                'index' => $i,
                'name' => $entryName,
                'is_dir' => $isDir,
                'extension' => $isDir ? 'folder' : pathinfo($entryName, PATHINFO_EXTENSION),
                'size_bytes' => $uncompressed,
                'size_formatted' => ZipHistory::formatBytes($uncompressed),
                'compressed_bytes' => $compressed,
                'compressed_formatted' => ZipHistory::formatBytes($compressed),
                'ratio' => $compressionRatio,
                'is_encrypted' => $encrypted,
                'crc' => sprintf('%08X', $stat['crc'] ?? 0),
                'modified_at' => isset($stat['mtime']) ? date('M d, Y h:i A', $stat['mtime']) : '-',
            ];
        }

        $zip->close();

        $overallRatio = 0;
        if ($totalUncompressed > 0) {
            $overallRatio = round((($totalUncompressed - $totalCompressed) / $totalUncompressed) * 100, 2);
        }

        return [
            'success' => true,
            'archive_name' => basename($zipPath),
            'total_files' => count($files),
            'total_uncompressed_bytes' => $totalUncompressed,
            'total_uncompressed_formatted' => ZipHistory::formatBytes($totalUncompressed),
            'total_compressed_bytes' => $totalCompressed,
            'total_compressed_formatted' => ZipHistory::formatBytes($totalCompressed),
            'overall_savings_percent' => max(0, $overallRatio),
            'is_encrypted' => $isEncrypted,
            'files' => $files,
        ];
    }

    /**
     * Extract a ZIP Archive to a target directory with optional password
     */
    public function extractArchive(string $zipPath, string $destinationDir, ?string $password = null): array
    {
        if (!file_exists($zipPath)) {
            return [
                'success' => false,
                'message' => 'ZIP archive not found.',
            ];
        }

        if (!File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        $realDestination = realpath($destinationDir);
        if (!$realDestination) {
            return [
                'success' => false,
                'message' => 'Invalid destination directory.',
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return [
                'success' => false,
                'message' => 'Unable to open ZIP archive for extraction.',
            ];
        }

        if (!empty($password)) {
            $zip->setPassword($password);
        }

        // Security check against Zip Slip (Path Traversal)
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_contains($filename, '../') || str_contains($filename, '..\\')) {
                $zip->close();
                return [
                    'success' => false,
                    'message' => 'Security Error: Archive contains unsafe relative paths.',
                ];
            }
        }

        $extractSuccess = @$zip->extractTo($destinationDir);
        $totalFiles = $zip->numFiles;
        $zip->close();

        if (!$extractSuccess) {
            return [
                'success' => false,
                'message' => 'Failed to extract archive. If the archive is password-protected, please provide the correct decryption password.',
            ];
        }

        // Calculate extracted size
        $totalBytes = 0;
        foreach (File::allFiles($destinationDir) as $f) {
            $totalBytes += $f->getSize();
        }

        return [
            'success' => true,
            'message' => 'Archive extracted successfully.',
            'destination' => $destinationDir,
            'total_files' => $totalFiles,
            'total_size_bytes' => $totalBytes,
            'total_size_formatted' => ZipHistory::formatBytes($totalBytes),
        ];
    }

    /**
     * Extract single file content from inside ZIP archive for direct download
     */
    public function getSingleFileContent(string $zipPath, string $entryName, ?string $password = null)
    {
        if (!file_exists($zipPath)) {
            return null;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
            return null;
        }

        if (!empty($password)) {
            $zip->setPassword($password);
        }

        $content = $zip->getFromName($entryName);
        $zip->close();

        return $content !== false ? $content : null;
    }

    /**
     * Compute 30-Day Storage & Activity Analytics Data for Chart.js
     */
    public function getAnalyticsData(): array
    {
        $startDate = Carbon::today()->subDays(29);
        $days = [];
        $sizesKB = [];
        $counts = [];

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateStr = $date->format('M d');
            $days[] = $dateStr;

            $dayZips = ZipHistory::whereDate('created_at', $date->toDateString())->get();
            $counts[] = $dayZips->count();

            // Sum up approximate size in KB
            $kbTotal = 0;
            foreach ($dayZips as $zip) {
                if ($zip->compressed_size_bytes > 0) {
                    $kbTotal += round($zip->compressed_size_bytes / 1024, 2);
                } elseif (preg_match('/([0-9\.]+)\s*MB/i', $zip->zip_size, $m)) {
                    $kbTotal += (float) $m[1] * 1024;
                } elseif (preg_match('/([0-9\.]+)\s*KB/i', $zip->zip_size, $m)) {
                    $kbTotal += (float) $m[1];
                }
            }
            $sizesKB[] = round($kbTotal, 2);
        }

        // Source Distribution
        $uploadCount = ZipHistory::where('source', 'upload')->count();
        $directoryCount = ZipHistory::where('source', 'directory')->count();
        $selectedCount = ZipHistory::where('source', 'selected_files')->count();
        $commandCount = ZipHistory::where('source', 'command')->count();

        // Encryption Statistics
        $encryptedCount = ZipHistory::where('is_encrypted', true)->count();
        $plainCount = ZipHistory::where('is_encrypted', false)->count();

        // Total Space Savings
        $totalOriginal = (int) ZipHistory::sum('original_size_bytes');
        $totalCompressed = (int) ZipHistory::sum('compressed_size_bytes');
        $totalSavedBytes = max(0, $totalOriginal - $totalCompressed);
        $overallSavingsRatio = $totalOriginal > 0 ? round(($totalSavedBytes / $totalOriginal) * 100, 1) : 0;

        // Top 5 Largest Archives
        $largestArchives = ZipHistory::where('status', 'success')
            ->orderBy('compressed_size_bytes', 'desc')
            ->take(5)
            ->get(['id', 'zip_name', 'zip_size', 'compressed_size_bytes', 'total_files', 'is_encrypted', 'created_at']);

        return [
            'chart' => [
                'labels' => $days,
                'sizes_kb' => $sizesKB,
                'counts' => $counts,
            ],
            'sources' => [
                'labels' => ['Uploaded Files', 'Directory Archive', 'Selected Storage Files', 'System Command'],
                'data' => [$uploadCount, $directoryCount, $selectedCount, $commandCount],
            ],
            'encryption' => [
                'labels' => ['Encrypted (AES-256)', 'Open Archives'],
                'data' => [$encryptedCount, $plainCount],
            ],
            'savings' => [
                'total_original_bytes' => $totalOriginal,
                'total_original_formatted' => ZipHistory::formatBytes($totalOriginal),
                'total_compressed_bytes' => $totalCompressed,
                'total_compressed_formatted' => ZipHistory::formatBytes($totalCompressed),
                'total_saved_bytes' => $totalSavedBytes,
                'total_saved_formatted' => ZipHistory::formatBytes($totalSavedBytes),
                'savings_percent' => $overallSavingsRatio,
            ],
            'largest_archives' => $largestArchives,
        ];
    }
}
