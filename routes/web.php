<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicPaymentController;
use App\Http\Controllers\DashboardController;
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

Route::prefix('pay')->group(function() {
    Route::get('/{payment}', [PublicPaymentController::class, 'show'])->name('public.payment');
    Route::post('/{payment}/personal', [PublicPaymentController::class, 'personal']);
    Route::post('/{payment}/checkout', [PublicPaymentController::class, 'checkout']);
    Route::get('/{payment}/receipt', [PublicPaymentController::class, 'receipt']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['verified'])->name('dashboard');

    Route::prefix('payments')->group(function() {
        Route::get('/', [PaymentController::class, 'index'])->name('payments');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('payment.show');
        Route::get('/{payment}/toggle-active', [PaymentController::class, 'toggleActive'])->name('payment.toggle-active');
        Route::get('/{payment}/mark-as-paid', [PaymentController::class, 'markAsPaid'])->name('payment.mark-as-paid');

        Route::post('/{payment}', [PaymentController::class, 'update'])->name('payment.update');
        Route::post('/', [PaymentController::class, 'store']);
    });

    Route::prefix('customers')->group(function() {
        Route::get('/', [CustomerController::class, 'index'])->name('customers');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        // Route::get('/{payment}/toggle-active', [PaymentController::class, 'toggleActive'])->name('payment.toggle-active');
        // Route::get('/{payment}/mark-as-paid', [PaymentController::class, 'markAsPaid'])->name('payment.mark-as-paid');

        // Route::post('/{payment}', [PaymentController::class, 'update'])->name('payment.update');
        // Route::post('/', [PaymentController::class, 'store']);
    });
});

require __DIR__.'/auth.php';
