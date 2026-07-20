<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZipController;

Route::get('/', function () {
    return redirect()->route('zip.index');
});

/*
|--------------------------------------------------------------------------
| ZIP Archive Routes
|--------------------------------------------------------------------------
*/

Route::controller(ZipController::class)->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/zip', 'index')->name('zip.index');

    /*
    |--------------------------------------------------------------------------
    | Create ZIP
    |--------------------------------------------------------------------------
    */

    Route::post('/zip/create', 'createZip')
        ->name('zip.create');

    Route::post('/zip/create-from-files', 'createZipFromFiles')
        ->name('zip.create.from.files');

    Route::post('/zip/create-from-upload', 'createZipFromUpload')
        ->name('zip.create.from.upload');

    Route::get('/zip/create-from-directory', 'createZipFromDirectory')
        ->name('zip.create.from.directory');

    /*
    |--------------------------------------------------------------------------
    | File List API
    |--------------------------------------------------------------------------
    */

    Route::get('/zip/list-files', 'listFiles')
        ->name('zip.list.files');

    /*
    |--------------------------------------------------------------------------
    | Delete History
    |--------------------------------------------------------------------------
    */

    Route::delete('/zip/history/{id}', 'destroy')
        ->name('zip.history.destroy');
});
