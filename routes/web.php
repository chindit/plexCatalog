<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ServerController;
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

Route::middleware(['auth'])->group(function() {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/sync', [DashboardController::class, 'sync'])->name('sync');
    Route::get('/server/add', [ServerController::class, 'server'])->name('add_server');
    Route::post('/server/add', [ServerController::class, 'createServer'])->name('create_server');
    Route::post('/api/token', [ApiController::class, 'getPlexToken']);

    Route::get('/medias', [MediaController::class, 'index']);
    Route::get('/catalog', [DashboardController::class, 'catalog'])->name('catalog');
    Route::post('/catalog', [DashboardController::class, 'createCatalog'])->name('create_catalog');
});

require __DIR__.'/auth.php';
