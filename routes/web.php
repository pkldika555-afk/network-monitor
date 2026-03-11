<?php

use App\Http\Controllers\CheckController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/services', [ServiceController::class, 'index'])->name('services.index')->middleware('auth');
Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
Route::put('/services/{service}',[ServiceController::class, 'update'])->name('services.update');
Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
Route::post('/services/{service}/assign', [ServiceController::class, 'assign'])->name('services.assign');

Route::post('/check/{id}',  [CheckController::class, 'single'])->name('check.single');
Route::post('/check-all',   [CheckController::class, 'all'])->name('check.all');

Route::get('/login',  [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout',[LoginController::class, 'destroy'])->name('logout');

Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy'])->middleware('auth');