<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/upload', [App\Http\Controllers\FileController::class, 'upload']);
Route::get('/files', [App\Http\Controllers\FileController::class, 'getFiles']);
Route::put('/files/{id}', [App\Http\Controllers\FileController::class, 'updateDescription']);
Route::delete('/files/{id}', [App\Http\Controllers\FileController::class, 'deleteFile']);
Route::delete('/files', [App\Http\Controllers\FileController::class, 'deleteAllFiles']);
