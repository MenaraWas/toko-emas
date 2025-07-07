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

Route::middleware(['auth'])->group(function () {
    Route::get('/switch-branch/{branchId}', function ($branchId) {
        if ($branchId === 'all') {
            session()->forget('active_branch_id');
            return back()->with('success', 'Berhasil reset cabang aktif.');
        }

        session(['active_branch_id' => $branchId]);
        return back()->with('success', 'Cabang aktif diganti.');
    })->name('switch-branch');
});
