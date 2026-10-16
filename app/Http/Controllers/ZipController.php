<?php

namespace App\Http\Controllers;

use App\Models\ZipExtractedHistory;
use App\Models\ZipHistory;
use App\Services\ZipArchiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ZipController extends Controller
{
    protected ZipArchiveService $zipService;

    public function __construct(ZipArchiveService $zipService)
    {
        $this->zipService = $zipService;
    }

    /**
     * Dashboard with Analytics, Creation forms, Extractor & History
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $filterEncrypted = $request->encrypted;
        $filterSource = $request->source;

        $histories = ZipHistory::when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('zip_name', 'like', "%{$search}%")
                    ->orWhere('total_files', 'like', "%{$search}%")
                    ->orWhere('zip_size', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhere('checksum_sha256', 'like', "%{$search}%")
                    ->orWhereDate('created_at', $search);
            });
        })
        ->when($filterEncrypted !== null && $filterEncrypted !== '', function ($query) use ($filterEncrypted) {
            $query->where('is_encrypted', $filterEncrypted === '1');
        })
        ->when($filterSource, function ($query) use ($filterSource) {
            $query->where('source', $filterSource);
        })
        ->latest('id')
        ->paginate(10)
        ->withQueryString();

        $totalZipFiles = ZipHistory::count();
        $todayZipFiles = ZipHistory::whereDate('created_at', today())->count();
        $successfulZipFiles = ZipHistory::where('status', 'success')->count();
        $failedZipFiles = ZipHistory::where('status', 'failed')->count();
        $encryptedZipFiles = ZipHistory::where('is_encrypted', true)->count();

        // Calculate Total Space Savings
        $totalOriginal = (int) ZipHistory::sum('original_size_bytes');
        $totalCompressed = (int) ZipHistory::sum('compressed_size_bytes');
        $totalSavedBytes = max(0, $totalOriginal - $totalCompressed);
        $totalSavedFormatted = ZipHistory::formatBytes($totalSavedBytes);

        $latestZip = ZipHistory::latest()->first();
        $largestZip = ZipHistory::orderBy('compressed_size_bytes', 'desc')->first();

        $analytics = $this->zipService->getAnalyticsData();

        return view('zip.index', compact(
            'histories',
            'search',
            'filterEncrypted',
            'filterSource',
            'totalZipFiles',
            'todayZipFiles',
            'successfulZipFiles',
            'failedZipFiles',
            'encryptedZipFiles',
            'totalSavedFormatted',
            'totalSavedBytes',
            'latestZip',
            'largestZip',
            'analytics'
        ));
    }

    /**
     * Create ZIP from uploaded files with password encryption and compression options
     */
    public function createZipFromUpload(Request $request)
    {
        $request->validate([
            'uploaded_files'   => 'required|array',
            'uploaded_files.*' => 'required|file|max:20480', // 20 MB per file
            'password'         => 'nullable|string|min:4',
            'compression_level'=> 'nullable|in:store,normal,maximum',
            'exclude_ext'      => 'nullable|string',
        ]);

        $zipFileName = 'uploaded_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/public/zips/' . $zipFileName);

        $tempFiles = [];
        $filesToArchive = [];

        // Save uploaded files to temp folder for archiving
        foreach ($request->file('uploaded_files') as $file) {
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $tempMoved = $file->move(storage_path('app/temp'), time() . '_' . $safeName);
            $tempRealPath = $tempMoved->getRealPath();

            $tempFiles[] = $tempRealPath;
            $filesToArchive[] = [
                'path' => $tempRealPath,
                'name' => $safeName,
            ];
        }

        $excludeArray = [];
        if ($request->filled('exclude_ext')) {
            $excludeArray = explode(',', str_replace([' ', '.'], ['', ''], $request->exclude_ext));
        }

        $options = [
            'password' => $request->password,
            'compression_level' => $request->input('compression_level', 'normal'),
            'exclude_extensions' => $excludeArray,
        ];

        $result = $this->zipService->createArchive($zipPath, $filesToArchive, $options);

        // Cleanup temporary raw files
        foreach ($tempFiles as $temp) {
            if (file_exists($temp)) {
                @unlink($temp);
            }
        }

        if ($result['success']) {
            $zipSizeFormatted = ZipHistory::formatBytes($result['compressed_size_bytes']);

            $this->saveHistoryRecord([
                'zip_name' => $zipFileName,
                'total_files' => $result['total_files'],
                'zip_size' => $zipSizeFormatted,
                'zip_path' => $zipPath,
                'source' => 'upload',
                'status' => 'success',
                'remarks' => 'Created from uploaded files (' . ($result['is_encrypted'] ? 'Encrypted AES-256' : 'Open') . ', ' . ucfirst($result['compression_level']) . ').',
                'is_encrypted' => $result['is_encrypted'],
                'encryption_method' => $result['encryption_method'],
                'compression_level' => $result['compression_level'],
                'original_size_bytes' => $result['original_size_bytes'],
                'compressed_size_bytes' => $result['compressed_size_bytes'],
                'savings_percent' => $result['savings_percent'],
                'checksum_sha256' => $result['checksum_sha256'],
            ]);

            return response()->download($zipPath);
        }

        $this->saveHistoryRecord([
            'zip_name' => $zipFileName,
            'total_files' => 0,
            'zip_size' => '0 KB',
            'zip_path' => $zipPath,
            'source' => 'upload',
            'status' => 'failed',
            'remarks' => $result['message'],
        ]);

        return back()->with('error', 'Failed to create ZIP: ' . $result['message']);
    }

    /**
     * Create ZIP from selected storage files
     */
    public function createZipFromFiles(Request $request)
    {
        $request->validate([
            'selected_files'   => 'required|array',
            'selected_files.*' => 'required|string',
            'password'         => 'nullable|string|min:4',
            'compression_level'=> 'nullable|in:store,normal,maximum',
            'exclude_ext'      => 'nullable|string',
        ]);

        $zipFileName = 'selected_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/public/zips/' . $zipFileName);

        $filesToArchive = [];
        foreach ($request->selected_files as $filePath) {
            if (Storage::exists($filePath)) {
                $filesToArchive[] = [
                    'path' => Storage::path($filePath),
                    'name' => basename($filePath),
                ];
            }
        }

        $excludeArray = [];
        if ($request->filled('exclude_ext')) {
            $excludeArray = explode(',', str_replace([' ', '.'], ['', ''], $request->exclude_ext));
        }

        $options = [
            'password' => $request->password,
            'compression_level' => $request->input('compression_level', 'normal'),
            'exclude_extensions' => $excludeArray,
        ];

        $result = $this->zipService->createArchive($zipPath, $filesToArchive, $options);

        if ($result['success']) {
            $zipSizeFormatted = ZipHistory::formatBytes($result['compressed_size_bytes']);

            $this->saveHistoryRecord([
                'zip_name' => $zipFileName,
                'total_files' => $result['total_files'],
                'zip_size' => $zipSizeFormatted,
                'zip_path' => $zipPath,
                'source' => 'selected_files',
                'status' => 'success',
                'remarks' => 'Created from selected storage files (' . ($result['is_encrypted'] ? 'Encrypted' : 'Open') . ').',
                'is_encrypted' => $result['is_encrypted'],
                'encryption_method' => $result['encryption_method'],
                'compression_level' => $result['compression_level'],
                'original_size_bytes' => $result['original_size_bytes'],
                'compressed_size_bytes' => $result['compressed_size_bytes'],
                'savings_percent' => $result['savings_percent'],
                'checksum_sha256' => $result['checksum_sha256'],
            ]);

            return response()->download($zipPath);
        }

        $this->saveHistoryRecord([
            'zip_name' => $zipFileName,
            'total_files' => 0,
            'zip_size' => '0 KB',
            'zip_path' => $zipPath,
            'source' => 'selected_files',
            'status' => 'failed',
            'remarks' => $result['message'],
        ]);

        return back()->with('error', 'Failed to create ZIP: ' . $result['message']);
    }

    /**
     * Create ZIP from storage directory
     */
    public function createZipFromDirectory(Request $request)
    {
        $directory = storage_path('app/public/files');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $allFiles = File::allFiles($directory);
        if (count($allFiles) === 0) {
            return back()->with('error', 'No files found inside directory (storage/app/public/files).');
        }

        $zipFileName = 'directory_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/public/zips/' . $zipFileName);

        $filesToArchive = [];
        foreach ($allFiles as $file) {
            $filesToArchive[] = [
                'path' => $file->getRealPath(),
                'name' => $file->getRelativePathname(),
            ];
        }

        $excludeArray = [];
        if ($request->filled('exclude_ext')) {
            $excludeArray = explode(',', str_replace([' ', '.'], ['', ''], $request->exclude_ext));
        }

        $options = [
            'password' => $request->password,
            'compression_level' => $request->input('compression_level', 'normal'),
            'exclude_extensions' => $excludeArray,
        ];

        $result = $this->zipService->createArchive($zipPath, $filesToArchive, $options);

        if ($result['success']) {
            $zipSizeFormatted = ZipHistory::formatBytes($result['compressed_size_bytes']);

            $this->saveHistoryRecord([
                'zip_name' => $zipFileName,
                'total_files' => $result['total_files'],
                'zip_size' => $zipSizeFormatted,
                'zip_path' => $zipPath,
                'source' => 'directory',
                'status' => 'success',
                'remarks' => 'Created from storage directory (' . ($result['is_encrypted'] ? 'Encrypted' : 'Open') . ').',
                'is_encrypted' => $result['is_encrypted'],
                'encryption_method' => $result['encryption_method'],
                'compression_level' => $result['compression_level'],
                'original_size_bytes' => $result['original_size_bytes'],
                'compressed_size_bytes' => $result['compressed_size_bytes'],
                'savings_percent' => $result['savings_percent'],
                'checksum_sha256' => $result['checksum_sha256'],
            ]);

            return response()->download($zipPath);
        }

        return back()->with('error', 'Failed to create ZIP: ' . $result['message']);
    }

    /**
     * Online ZIP Extractor: Upload and extract ZIP archive with optional password
     */
    public function extractArchive(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:51200', // max 50 MB
            'password' => 'nullable|string',
        ]);

        $uploadedFile = $request->file('zip_file');
        $archiveName = $uploadedFile->getClientOriginalName();
        $targetFolderName = 'ext_' . time() . '_' . preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($archiveName, PATHINFO_FILENAME));
        $destinationDir = storage_path('app/extracted/' . $targetFolderName);

        $tempPath = $uploadedFile->getRealPath();

        $result = $this->zipService->extractArchive($tempPath, $destinationDir, $request->password);

        if ($result['success']) {
            ZipExtractedHistory::create([
                'archive_name' => $archiveName,
                'extracted_folder' => $targetFolderName,
                'total_files_extracted' => $result['total_files'],
                'total_size_bytes' => $result['total_size_bytes'],
                'status' => 'success',
                'extracted_at' => now(),
            ]);

            return back()->with('success', "Archive extracted successfully! Extracted {$result['total_files']} files ({$result['total_size_formatted']}) into folder '{$targetFolderName}'.");
        }

        ZipExtractedHistory::create([
            'archive_name' => $archiveName,
            'extracted_folder' => $targetFolderName,
            'total_files_extracted' => 0,
            'total_size_bytes' => 0,
            'status' => 'failed',
            'extracted_at' => now(),
        ]);

        return back()->with('error', 'Extraction failed: ' . $result['message']);
    }

    /**
     * Extract existing ZIP file from history
     */
    public function extractHistoryArchive(Request $request, $id)
    {
        $history = ZipHistory::findOrFail($id);

        if (!File::exists($history->zip_path)) {
            return back()->with('error', 'ZIP archive file is missing from server storage.');
        }

        $targetFolderName = 'ext_' . time() . '_' . preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($history->zip_name, PATHINFO_FILENAME));
        $destinationDir = storage_path('app/extracted/' . $targetFolderName);

        $result = $this->zipService->extractArchive($history->zip_path, $destinationDir, $request->password);

        if ($result['success']) {
            ZipExtractedHistory::create([
                'zip_history_id' => $history->id,
                'archive_name' => $history->zip_name,
                'extracted_folder' => $targetFolderName,
                'total_files_extracted' => $result['total_files'],
                'total_size_bytes' => $result['total_size_bytes'],
                'status' => 'success',
                'extracted_at' => now(),
            ]);

            return back()->with('success', "Archive extracted successfully! {$result['total_files']} files extracted ({$result['total_size_formatted']}).");
        }

        return back()->with('error', 'Failed to extract: ' . $result['message']);
    }

    /**
     * In-Archive File Explorer / Inspector API
     */
    public function inspectArchive($id)
    {
        $history = ZipHistory::findOrFail($id);

        if (!File::exists($history->zip_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Archive file not found on disk.',
            ], 404);
        }

        $data = $this->zipService->inspectArchive($history->zip_path);
        $data['history_id'] = $history->id;
        $data['checksum_sha256'] = $history->checksum_sha256;
        $data['encryption_method'] = $history->encryption_method;

        return response()->json($data);
    }

    /**
     * Selective Single File Download from within ZIP Archive
     */
    public function downloadSingleFile(Request $request, $id)
    {
        $history = ZipHistory::findOrFail($id);
        $filePath = $request->query('file');
        $password = $request->query('password');

        if (empty($filePath)) {
            return back()->with('error', 'Filename parameter is required.');
        }

        if (!File::exists($history->zip_path)) {
            return back()->with('error', 'ZIP archive not found on disk.');
        }

        $content = $this->zipService->getSingleFileContent($history->zip_path, $filePath, $password);

        if ($content === null) {
            return back()->with('error', 'Unable to retrieve file from inside archive. Verify password if protected.');
        }

        $filename = basename($filePath);
        return Response::make($content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download entire ZIP from history
     */
    public function downloadHistory($id)
    {
        $history = ZipHistory::findOrFail($id);

        if (!File::exists($history->zip_path)) {
            return back()->with('error', 'ZIP file not found on disk. Please create ZIP again.');
        }

        return response()->download($history->zip_path, $history->zip_name);
    }

    /**
     * Delete History & File
     */
    public function destroy($id)
    {
        $history = ZipHistory::findOrFail($id);

        if (File::exists($history->zip_path)) {
            File::delete($history->zip_path);
        }

        $history->delete();

        return redirect()->route('zip.index')->with('success', 'ZIP archive and history entry deleted successfully.');
    }

    /**
     * Chart.js 30-Day Storage & Activity Analytics API
     */
    public function analytics()
    {
        $data = $this->zipService->getAnalyticsData();
        return response()->json($data);
    }

    /**
     * List available files in public storage
     */
    public function listFiles()
    {
        $directory = 'public/files';

        if (!Storage::exists($directory)) {
            return response()->json([
                'success' => false,
                'message' => 'Directory not found.',
                'files' => [],
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
                'last_modified' => date('d M Y h:i A', Storage::lastModified($file)),
            ];
        }

        return response()->json([
            'success' => true,
            'total_files' => count($fileList),
            'files' => $fileList,
        ]);
    }

    /**
     * Helper to persist history
     */
    protected function saveHistoryRecord(array $data): ZipHistory
    {
        return ZipHistory::create(array_merge([
            'status' => 'success',
            'source' => 'upload',
            'is_encrypted' => false,
            'compression_level' => 'normal',
            'original_size_bytes' => 0,
            'compressed_size_bytes' => 0,
            'savings_percent' => 0.0,
        ], $data));
    }
}
