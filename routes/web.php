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
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contacts.confirm');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contacts.thanks');

Route::middleware('auth')->group(function () {

    // 管理画面のCRUDルート
    Route::get('admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])->name('admin.contacts.show');
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])->name('admin.contacts.destroy');
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');

    // タグのCRUDルート
    Route::post('/admin/tags', [TagController::class, 'store'])->name('admin.tags.store');
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit'])->name('admin.tags.edit');
    Route::put('/admin/tags/{tag}', [TagController::class, 'update'])->name('admin.tags.update');
    Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy'])->name('admin.tags.destroy');
});