<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FiscusController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SepaController;

// Debug endpoint (remove after debugging)
Route::get('debug-request', function () {
    return response()->json([
        'url' => request()->url(),
        'fullUrl' => request()->fullUrl(),
        'path' => request()->path(),
        'baseUrl' => request()->getBaseUrl(),
        'schemeAndHttpHost' => request()->getSchemeAndHttpHost(),
        'headers' => [
            'X-Forwarded-For' => request()->header('X-Forwarded-For'),
            'X-Forwarded-Proto' => request()->header('X-Forwarded-Proto'),
            'X-Forwarded-Host' => request()->header('X-Forwarded-Host'),
            'X-Forwarded-Port' => request()->header('X-Forwarded-Port'),
            'X-Forwarded-Prefix' => request()->header('X-Forwarded-Prefix'),
            'Host' => request()->header('Host'),
        ],
        'app_url' => config('app.url'),
        'auth' => auth()->check(),
    ]);
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('auth/login', [AuthController::class, 'getLogin'])->name('auth.login');
    Route::post('auth/authenticate', [AuthController::class, 'postAuthenticate'])->name('auth.authenticate');
});

// Public invoice check
Route::get('check-bill', [InvoiceController::class, 'getPerPerson'])->name('invoice.check-bill');
Route::post('invoice/setperson', [InvoiceController::class, 'postSetPerson'])->name('invoice.setperson');
Route::post('invoice/setpersonalinvoicegroup', [InvoiceController::class, 'postSetPersonalInvoiceGroup'])->name('invoice.setpersonalinvoicegroup');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Auth
    Route::get('auth/logout', [AuthController::class, 'getLogout'])->name('auth.logout');

    // Test route to debug - check if routes are registered
    Route::get('test-simple', function () {
        $routes = collect(app('router')->getRoutes())->map(function($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        });

        $memberRoutes = $routes->filter(function($route) {
            return str_contains($route['action'], 'MemberController') ||
                   str_contains($route['uri'], 'member');
        });

        $missingRoutes = [
            'member_index_exists' => $routes->contains(fn($r) => $r['name'] === 'member.index'),
            'product_index_exists' => $routes->contains(fn($r) => $r['name'] === 'product.index'),
            'group_index_exists' => $routes->contains(fn($r) => $r['name'] === 'group.index'),
            'member_test_exists' => $routes->contains(fn($r) => $r['name'] === 'member.test'),
        ];

        return response()->json([
            'total_routes' => $routes->count(),
            'member_routes' => $memberRoutes->values(),
            'missing_index_routes' => $missingRoutes,
        ]);
    });

    // Test route to debug - controller
    Route::get('test-home', [HomeController::class, 'getIndex'])->name('test.home');

    // Test with prefix
    Route::get('home-with-prefix', [HomeController::class, 'getIndex'])->name('home.prefixed');

    // Home
    Route::get('/', [HomeController::class, 'getIndex'])->name('home');

    // Orders
    Route::post('order/store/{type}', [OrderController::class, 'postStore'])->name('order.store');

    // Groups
    Route::prefix('group')->name('group.')->group(function () {
        Route::post('addmember', [GroupController::class, 'postAddMember'])->name('addmember');
        Route::delete('groupmember/{groupMember}', [GroupController::class, 'deleteGroupMember'])->name('group.member.destroy');
    });
    Route::resource('group', GroupController::class);

    // Test explicit member route
    Route::get('member-test', [MemberController::class, 'index'])->name('member.test');

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
