<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\LogController;
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
    return redirect()->route('login');
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


Route::get('/logs', [LogController::class, 'index'])->name('logs.index')->middleware('auth');

Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
Route::post('/backup/download', [BackupController::class, 'backup'])->name('backup.download');
Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');

Route::get('/restore-awal', [BackupController::class, 'restoreAwalForm'])->name('backup.restore-awal');
Route::post('/restore-awal', [BackupController::class, 'restoreAwal'])->name('backup.restore-awal.store');
Route::post('/logs/reset', [LogController::class, 'resetLogs'])->name('logs.reset');
