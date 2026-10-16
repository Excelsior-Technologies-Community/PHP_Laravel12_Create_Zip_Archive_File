<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZipController;

/*
|--------------------------------------------------------------------------
| Web Routes - Advanced ZIP Archive Manager & Analytics
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('zip.index');
});

Route::controller(ZipController::class)->group(function () {

    // Main Dashboard & Analytics
    Route::get('/zip', 'index')->name('zip.index');
    Route::get('/zip/api/analytics', 'analytics')->name('zip.analytics');

    // Archive Creation Actions (with Encryption & Compression Options)
    Route::post('/zip/create-from-upload', 'createZipFromUpload')->name('zip.create.from.upload');
    Route::post('/zip/create-from-files', 'createZipFromFiles')->name('zip.create.from.files');
    Route::get('/zip/create-from-directory', 'createZipFromDirectory')->name('zip.create.from.directory');

    // Archive Extraction (Online Extractor)
    Route::post('/zip/extract', 'extractArchive')->name('zip.extract');
    Route::post('/zip/history/extract/{id}', 'extractHistoryArchive')->name('zip.history.extract');

    // In-Archive File Explorer & Single File Extraction
    Route::get('/zip/inspect/{id}', 'inspectArchive')->name('zip.inspect');
    Route::get('/zip/download-file/{id}', 'downloadSingleFile')->name('zip.download.file');

    // File List API & History Management
    Route::get('/zip/list-files', 'listFiles')->name('zip.list.files');
    Route::get('/zip/history/download/{id}', 'downloadHistory')->name('zip.history.download');
    Route::delete('/zip/history/{id}', 'destroy')->name('zip.history.destroy');
});
