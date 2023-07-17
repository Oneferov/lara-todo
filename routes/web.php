<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Route::post('item', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::resources([
    'item' => App\Http\Controllers\ItemController::class,
]);

Route::delete('/item/{id}/image', [App\Http\Controllers\ItemController::class, 'destroyImage']);
Route::delete('/item/{item_id}/tag/{tag_id}', [App\Http\Controllers\ItemController::class, 'destroyTag']);
Route::post('/item/{id}/image', [App\Http\Controllers\ItemController::class, 'updateImage']);
Route::post('/item/{id}/tag', [App\Http\Controllers\ItemController::class, 'storeTag']);
