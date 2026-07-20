<?php

namespace App\Http\Controllers;

use App\Models\ZipHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class ZipController extends Controller
{
    /**
     * Dashboard
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $histories = ZipHistory::when($search, function ($query) use ($search) {

            $query->where(function ($q) use ($search) {

                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('zip_name', 'like', "%{$search}%")
                    ->orWhere('total_files', 'like', "%{$search}%")
                    ->orWhere('zip_size', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereDate('created_at', $search)
                    ->orWhereDate('updated_at', $search);
            });
        })

            ->oldest()
            ->paginate(5)
            ->withQueryString();

        $totalZipFiles = ZipHistory::count();

        $todayZipFiles = ZipHistory::whereDate(
            'created_at',
            today()
        )->count();

        $successfulZipFiles = ZipHistory::where(
            'status',
            'success'
        )->count();

        $failedZipFiles = ZipHistory::where(
            'status',
            'failed'
        )->count();

        $latestZip = ZipHistory::latest()->first();

        $largestZip = ZipHistory::orderByRaw("
            CAST(
                REPLACE(
                    REPLACE(zip_size,'MB',''),
                    'KB',''
                ) AS DECIMAL(10,2)
            ) DESC
        ")->first();

        return view('zip.index', compact(
            'histories',
            'search',
            'totalZipFiles',
            'todayZipFiles',
            'successfulZipFiles',
            'failedZipFiles',
            'latestZip',
            'largestZip'
        ));
    }

    /**
     * Save ZIP History
     */
    private function saveHistory(
        $zipName,
        $totalFiles,
        $zipPath,
        $source = 'upload',
        $status = 'success',
        $remarks = null
    ) {
        if (file_exists($zipPath)) {
            $size = number_format(filesize($zipPath) / 1024, 2) . ' KB';
        } else {
            $size = '0 KB';
        }

        ZipHistory::create([
            'zip_name'    => $zipName,
            'total_files' => $totalFiles,
            'zip_size'    => $size,
            'source'      => $source,
            'status'      => $status,
            'remarks'     => $remarks,
        ]);
    }

    /**
     * Delete History
     */
    public function destroy($id)
    {
        $history = ZipHistory::findOrFail($id);

        $history->delete();

        return redirect()
            ->route('zip.index')
            ->with(
                'success',
                'ZIP history deleted successfully.'
            );
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

        $zipFileName = 'archive_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        // Ensure directory exists
        if (!File::isDirectory(storage_path('app/public'))) {
            File::makeDirectory(storage_path('app/public'), 0755, true);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            $totalFiles = 0;

            foreach ($request->files as $group) {

                foreach ($group as $filePath) {

                    if (Storage::exists($filePath)) {

                        $zip->addFile(
                            Storage::path($filePath),
                            basename($filePath)
                        );

                        $totalFiles++;
                    }
                }
            }

            $zip->close();

            // Save ZIP History
            $this->saveHistory(
                $zipFileName,
                $totalFiles,
                $zipPath,
                'selected_files',
                'success',
                'ZIP created from selected storage files.'
            );

            return response()
                ->download($zipPath)
                ->deleteFileAfterSend(true);
        }

        // Save Failed History
        $this->saveHistory(
            $zipFileName,
            0,
            $zipPath,
            'selected_files',
            'failed',
            'Failed to create ZIP archive.'
        );

        return back()->with(
            'error',
            'Failed to create zip archive.'
        );
    }

    /**
     * Create ZIP from selected storage files
     */
    public function createZipFromFiles(Request $request)
    {
        $request->validate([
            'selected_files' => 'required|array',
            'selected_files.*' => 'required|string'
        ]);

        // Ensure temp directory exists
        if (!File::isDirectory(storage_path('app/temp'))) {
            File::makeDirectory(storage_path('app/temp'), 0755, true);
        }

        $zipFileName = 'selected_files_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            $totalFiles = 0;

            foreach ($request->selected_files as $filePath) {

                if (Storage::exists($filePath)) {

                    $zip->addFile(
                        Storage::path($filePath),
                        basename($filePath)
                    );

                    $totalFiles++;
                }
            }

            $zip->close();

            // Save ZIP history
            $this->saveHistory(
                $zipFileName,
                $totalFiles,
                $zipPath,
                'selected_files',
                'success',
                'ZIP created from selected files.'
            );

            return response()
                ->download($zipPath)
                ->deleteFileAfterSend(true);
        }

        // Save failed history
        $this->saveHistory(
            $zipFileName,
            0,
            $zipPath,
            'selected_files',
            'failed',
            'Unable to create ZIP archive.'
        );

        return back()->with(
            'error',
            'Failed to create ZIP archive.'
        );
    }

    /**
     * Create ZIP from uploaded files
     */
    public function createZipFromUpload(Request $request)
    {
        $request->validate([
            'uploaded_files'   => 'required|array',
            'uploaded_files.*' => 'required|file|max:10240', // 10 MB
        ]);

        // Create temp directory if it doesn't exist
        if (!File::isDirectory(storage_path('app/temp'))) {
            File::makeDirectory(storage_path('app/temp'), 0755, true);
        }

        $zipFileName = 'uploaded_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            $tempFiles = [];
            $totalFiles = 0;

            foreach ($request->file('uploaded_files') as $file) {

                // Safe filename
                $safeFileName = preg_replace(
                    '/[^A-Za-z0-9._-]/',
                    '_',
                    $file->getClientOriginalName()
                );

                // Move file to temp folder
                $file->move(
                    storage_path('app/temp'),
                    $safeFileName
                );

                $tempFile = storage_path('app/temp/' . $safeFileName);

                if (file_exists($tempFile)) {

                    $zip->addFile(
                        $tempFile,
                        $safeFileName
                    );

                    $tempFiles[] = $tempFile;

                    $totalFiles++;
                }
            }

            $zip->close();

            // Save ZIP history
            $this->saveHistory(
                $zipFileName,
                $totalFiles,
                $zipPath,
                'upload',
                'success',
                'ZIP created from uploaded files.'
            );

            // Delete uploaded temp files
            foreach ($tempFiles as $tempFile) {

                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }

            return response()
                ->download($zipPath)
                ->deleteFileAfterSend(true);
        }

        // Save failed history
        $this->saveHistory(
            $zipFileName,
            0,
            $zipPath,
            'upload',
            'failed',
            'Unable to create ZIP archive from uploaded files.'
        );

        return back()->with(
            'error',
            'Failed to create ZIP archive.'
        );
    }

    /**
     * Create ZIP from storage directory
     */
    public function createZipFromDirectory()
    {
        $directory = storage_path('app/public/files');

        // Check directory exists
        if (!File::exists($directory)) {

            return back()->with(
                'error',
                'Directory not found.'
            );
        }

        // Create temp directory
        if (!File::isDirectory(storage_path('app/temp'))) {

            File::makeDirectory(
                storage_path('app/temp'),
                0755,
                true
            );
        }

        $zipFileName = 'directory_' . now()->format('Y-m-d_H-i-s') . '.zip';

        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            $files = File::allFiles($directory);

            if (count($files) == 0) {

                $zip->close();

                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }

                return back()->with(
                    'error',
                    'No files found inside the directory.'
                );
            }

            $totalFiles = 0;

            foreach ($files as $file) {

                $relativePath = $file->getRelativePathname();

                $zip->addFile(
                    $file->getRealPath(),
                    $relativePath
                );

                $totalFiles++;
            }

            $zip->close();

            // Save ZIP history
            $this->saveHistory(
                $zipFileName,
                $totalFiles,
                $zipPath,
                'directory',
                'success',
                'ZIP created from storage directory.'
            );

            return response()
                ->download($zipPath)
                ->deleteFileAfterSend(true);
        }

        // Save failed history
        $this->saveHistory(
            $zipFileName,
            0,
            $zipPath,
            'directory',
            'failed',
            'Failed to create ZIP from directory.'
        );

        return back()->with(
            'error',
            'Failed to create ZIP archive.'
        );
    }

    /**
     * List available files
     */
    public function listFiles()
    {
        $directory = 'public/files';

        if (!Storage::exists($directory)) {

            return response()->json([
                'success' => false,
                'message' => 'Directory not found.',
                'files' => []
            ]);
        }

        $files = Storage::allFiles($directory);

        $fileList = [];

        foreach ($files as $file) {

            $fileList[] = [
                'name' => basename($file),
                'path' => $file,
                'extension' => pathinfo($file, PATHINFO_EXTENSION),
                'size' => number_format(Storage::size($file) / 1024, 2) . ' KB',
                'last_modified' => date(
                    'd M Y h:i A',
                    Storage::lastModified($file)
                ),
            ];
        }

        return response()->json([
            'success' => true,
            'total_files' => count($fileList),
            'files' => $fileList
        ]);
    }
}
