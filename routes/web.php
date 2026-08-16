<?php

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
    view('welcome');
    // return view('welcome');
});


// 仮ルート（Chapter 6で本実装に置き換え）
Route::middleware('auth')->group(function () {

    // タグの仮ルート（次のセクションで本実装に置き換え）
    // Route::resource('tags', TagController::class);
    Route::get('/tags', fn() => 'タグ一覧（準備中）')->name('tags.index');

    // お問い合わせのCRUDルート
    // Route::resource('contacts', ContactController::class);
    Route::get('/contacts', fn() => 'お問い合わせ一覧（準備中）')->name('contacts.index');

});