<?php

use App\Http\Controllers\ProductUploadController;
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

Route::view('/', 'uploads.index')->name('dashboard');

Route::get('/product-uploads', [ProductUploadController::class, 'index'])
    ->name('product-uploads.index');

Route::post('/product-uploads', [ProductUploadController::class, 'store'])
    ->name('product-uploads.store');
