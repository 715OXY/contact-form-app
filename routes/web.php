<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CategoryController;

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

    // タグの仮ルート（次のセクションで本実装に置き換え）
    // Route::resource('tags', TagController::class);
    Route::get('/tags', fn() => 'タグ一覧（準備中）')->name('tags.index');

    // お問い合わせのCRUDルート
    // Route::resource('contacts', ContactController::class);
    Route::get('/contacts', fn() => 'お問い合わせ一覧（準備中）')->name('contacts.index');

});