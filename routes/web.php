<?php

use App\Http\Controllers\PlexController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// The catalog endpoint receives Plex credentials, so generation remains POST-only.
// Redirect direct visits or browser refreshes back to the form instead of returning
// a method-not-allowed error.
Route::post('/catalog', [PlexController::class, 'listCatalogs'])->name('catalog');
Route::post('/report', [PlexController::class, 'generateReport'])->name('report');
Route::get('/reports/{token}', [PlexController::class, 'downloadReport'])->name('report.download');
