<?php

use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/upload', [FileController::class, 'store'])->name('api.upload');
Route::get('/download/{token}', [FileController::class, 'download'])->name('api.download');
Route::get('/uploads/stats/{token}', [FileController::class, 'stats'])->name('api.uploads.stats');
