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
    return view('welcome');
});

//menunggu POS Controller
// Route::middleware(['auth', 'role:Owner,SuperAdmin,Kasir'])->group(function () {
//     Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
// });
