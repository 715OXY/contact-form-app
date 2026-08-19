<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;

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


// お問い合わせのCRUDルート
Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contacts.confirm');
// Route::post('/contacts/store', [ContactController::class, 'store'])->name('contacts.store');
// Route::resource('contacts', ContactController::class)->only(['create', 'store']);

Route::middleware('auth')->group(function () {

    // 管理画面のCRUDルート
    Route::resource('admin', AdminController::class);
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])->name('admin.contacts.show');
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])->name('admin.contacts.destroy');

    // タグのCRUDルート
    // Route::resource('tags', TagController::class);

});