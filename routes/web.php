<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FiscusController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SepaController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('auth/login', [AuthController::class, 'getLogin'])->name('auth.login');
    Route::post('auth/authenticate', [AuthController::class, 'postAuthenticate'])->name('auth.authenticate');
});

// Public invoice check
Route::get('check-bill', [InvoiceController::class, 'getPerPerson'])->name('invoice.check-bill');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Auth
    Route::get('auth/logout', [AuthController::class, 'getLogout'])->name('auth.logout');

    // Home
    Route::get('/', [HomeController::class, 'getIndex'])->name('home');

    // Invoice management
    Route::prefix('invoice')->name('invoice.')->group(function () {
        Route::post('setperson', [InvoiceController::class, 'postSetPerson'])->name('setperson');
        Route::post('setpersonalinvoicegroup', [InvoiceController::class, 'postSetPersonalInvoiceGroup'])->name('setpersonalinvoicegroup');
    });

    // Orders
    Route::post('order/store/{type}', [OrderController::class, 'postStore'])->name('order.store');

    // Groups
    Route::prefix('group')->name('group.')->group(function () {
        Route::post('addmember', [GroupController::class, 'postAddMember'])->name('addmember');
        Route::delete('groupmember/{groupMember}', [GroupController::class, 'deleteGroupMember'])->name('group.member.destroy');
    });
    Route::resource('group', GroupController::class);

    // Resources
    Route::resource('product', ProductController::class);
    Route::resource('member', MemberController::class);
});

// Admin routes
Route::middleware(['auth', 'can:admin'])->group(function () {
    // SEPA
    Route::prefix('sepa')->name('sepa.')->group(function () {
        Route::get('download/{filename}', [SepaController::class, 'download'])->name('download');
    });
    Route::resource('sepa', SepaController::class)->only(['index', 'store']);

    // Fiscus
    Route::prefix('fiscus')->name('fiscus.')->group(function () {
        Route::get('invoiceprices/{invoiceProduct}', [FiscusController::class, 'getInvoiceprices'])->name('invoiceprices');
        Route::get('allinvoicelines/{invoiceProduct}', [FiscusController::class, 'getAllinvoicelines'])->name('allinvoicelines');
        Route::get('specificinvoicelines/{invoiceProductPrice}', [FiscusController::class, 'getSpecificinvoicelines'])->name('specificinvoicelines');
        Route::get('edit', [FiscusController::class, 'getEdit'])->name('edit');
    });
    Route::resource('fiscus', FiscusController::class)->except(['edit'])->parameters(['fiscus' => 'invoiceProduct']);

    // Invoice admin
    Route::prefix('invoice')->name('invoice.')->group(function () {
        Route::get('/', [InvoiceController::class, 'getIndex'])->name('index');
        Route::get('pdf', [InvoiceController::class, 'getPdf'])->name('pdf');
        Route::get('excel', [InvoiceController::class, 'getExcel'])->name('excel');
        Route::get('sepa', [InvoiceController::class, 'getSepa'])->name('sepa');
        Route::post('storeinvoicegroup', [InvoiceController::class, 'postStoreinvoicegroup'])->name('storeinvoicegroup');
        Route::post('selectinvoicegroup', [InvoiceController::class, 'postSelectinvoicegroup'])->name('selectinvoicegroup');
    });
});
