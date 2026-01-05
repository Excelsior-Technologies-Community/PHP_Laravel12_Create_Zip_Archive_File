<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class ZipController extends Controller
{
    /**
     * Show the zip creation form
     */
    public function index()
    {
        return view('zip.index');
    }

    /**
     * Create and download a zip file
     */
    public function createZip(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|string'
        ]);

        $zipFileName = 'archive_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        // Create a new zip archive
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($request->files as $file) {
                // Add files to the zip
                foreach ($file as $filePath) {
                    if (Storage::exists($filePath)) {
                        $zip->addFile(
                            Storage::path($filePath),
                            basename($filePath)
                        );
                    }
                }
            }

            $zip->close();

            // Return the zip file as download
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Failed to create zip archive');
    }

    /**
     * Create zip from selected files
     */
    public function createZipFromFiles(Request $request)
    {
        $request->validate([
            'selected_files' => 'required|array',
            'selected_files.*' => 'required|string'
        ]);

        $zipFileName = 'selected_files_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($request->selected_files as $filePath) {
                $fullPath = storage_path('app/' . $filePath);

                if (file_exists($fullPath)) {
                    $relativeName = basename($filePath);
                    $zip->addFile($fullPath, $relativeName);
                }
            }

            $zip->close();

            if (file_exists($zipPath)) {
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
        }

        return back()->with('error', 'Failed to create zip archive');
    }

    /**
     * Create zip from uploaded files
     */
    /**
     * Create zip from uploaded files
     */
    public function createZipFromUpload(Request $request)
    {
        $request->validate([
            'uploaded_files' => 'required|array',
            'uploaded_files.*' => 'required|file|max:10240' // 10MB max per file
        ]);

        $zipFileName = 'uploaded_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!File::isDirectory(storage_path('app/temp'))) {
            File::makeDirectory(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $tempFiles = [];

            foreach ($request->file('uploaded_files') as $file) {
                $fileName = $file->getClientOriginalName();

                // Sanitize file name to prevent path traversal
                $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);

                // Store file temporarily in temp directory
                $tempPath = storage_path('app/temp/' . $safeFileName);

                // Move uploaded file to temp location
                $file->move(storage_path('app/temp'), $safeFileName);

                // Check if file exists before adding to zip
                if (file_exists($tempPath)) {
                    // Add to zip with original/safe name
                    $zip->addFile($tempPath, $safeFileName);
                    $tempFiles[] = $tempPath; // Track for cleanup
                } else {
                    \Log::error("Temp file not created: " . $tempPath);
                }
            }

            $zip->close();

            // Clean up temporary files
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }

            if (file_exists($zipPath)) {
                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                \Log::error("Zip file not created at: " . $zipPath);
            }
        } else {
            \Log::error("Failed to open zip archive at: " . $zipPath);
        }

        return back()->with('error', 'Failed to create zip archive. Check logs for details.');
    }

    /**
     * Create zip from directory
     */
    public function createZipFromDirectory()
    {
        $directory = storage_path('app/public/files');
        $zipFileName = 'directory_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = File::allFiles($directory);

            foreach ($files as $file) {
                $relativePath = 'files/' . $file->getRelativePathname();
                $zip->addFile($file->getRealPath(), $relativePath);
            }

            $zip->close();

            if (file_exists($zipPath)) {
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
        }

        return back()->with('error', 'Failed to create zip archive');
    }

    /**
     * List available files for selection
     */
    public function listFiles()
    {
        $files = Storage::files('public/files');
        $fileList = [];

        foreach ($files as $file) {
            $fileList[] = [
                'path' => $file,
                'name' => basename($file),
                'size' => Storage::size($file),
                'modified' => Storage::lastModified($file)
            ];
        }

        return response()->json($fileList);
    }
}