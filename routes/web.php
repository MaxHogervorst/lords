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

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('auth/login', [AuthController::class, 'getLogin'])->name('auth.login');
    Route::post('auth/authenticate', [AuthController::class, 'postAuthenticate'])->name('auth.authenticate');
});

// Public invoice check
Route::get('check-bill', [InvoiceController::class, 'getPerPerson'])->name('invoice.check-bill');
Route::post('check-bill', [InvoiceController::class, 'postCheckBill'])->name('invoice.check-bill.post');
Route::post('invoice/setperson', [InvoiceController::class, 'postSetPerson'])->name('invoice.setperson');
Route::post('invoice/setpersonalinvoicegroup', [InvoiceController::class, 'postSetPersonalInvoiceGroup'])->name('invoice.setpersonalinvoicegroup');

// Debug routes to check proxy headers and IP detection
Route::get('debug/test', function () {
    return response('DEBUG TEST WORKING - ' . now(), 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('debug/raw', function () {
    $request = request();

    $output = "=== RAW SERVER VARS ===\n\n";
    $output .= "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'not set') . "\n";
    $output .= "HTTP_X_FORWARDED_FOR: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'not set') . "\n";
    $output .= "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set') . "\n";
    $output .= "HTTP_X_FORWARDED_HOST: " . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'not set') . "\n";
    $output .= "HTTP_X_FORWARDED_PORT: " . ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? 'not set') . "\n";
    $output .= "HTTP_X_REAL_IP: " . ($_SERVER['HTTP_X_REAL_IP'] ?? 'not set') . "\n";

    $output .= "\n=== LARAVEL REQUEST ===\n\n";
    $output .= "request()->ip(): " . $request->ip() . "\n";
    $output .= "request()->secure(): " . ($request->secure() ? 'true' : 'false') . "\n";
    $output .= "request()->getScheme(): " . $request->getScheme() . "\n";
    $output .= "request()->getClientIp(): " . $request->getClientIp() . "\n";

    $output .= "\n=== TRUST PROXIES DEBUG ===\n\n";
    $output .= "getTrustedProxies(): " . json_encode($request->getTrustedProxies()) . "\n";
    $output .= "getTrustedHeaderSet(): " . $request->getTrustedHeaderSet() . "\n";
    $output .= "getClientIps(): " . json_encode($request->getClientIps()) . "\n";

    return response($output, 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('debug/headers', function () {
    try {
        $data = [
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'client_ip' => request()->ip(),
            'server_remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'not set',
            'x_forwarded_for' => request()->header('X-Forwarded-For'),
            'x_forwarded_proto' => request()->header('X-Forwarded-Proto'),
            'x_forwarded_host' => request()->header('X-Forwarded-Host'),
            'x_forwarded_port' => request()->header('X-Forwarded-Port'),
            'x_real_ip' => request()->header('X-Real-IP'),
            'cf_connecting_ip' => request()->header('CF-Connecting-IP'),  // Check if Cloudflare sends this
            'is_secure' => request()->secure(),
            'scheme' => request()->getScheme(),
            'server_vars' => [
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
                'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
                'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null,
                'HTTP_X_REAL_IP' => $_SERVER['HTTP_X_REAL_IP'] ?? null,
                'HTTP_CF_CONNECTING_IP' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            ],
            'cloudflare_headers' => [
                'cf-connecting-ip' => request()->header('CF-Connecting-IP'),
                'cf-ipcountry' => request()->header('CF-IPCountry'),
                'cf-ray' => request()->header('CF-Ray'),
                'cf-visitor' => request()->header('CF-Visitor'),
            ],
            'digitalocean_headers' => [
                'do-connecting-ip' => request()->header('DO-Connecting-IP'),
                'x-real-ip' => request()->header('X-Real-IP'),
            ],
        ];

        return response()->json($data, 200, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ], 500, ['Content-Type' => 'application/json']);
    }
})->name('debug.headers');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Auth
    Route::get('auth/logout', [AuthController::class, 'getLogout'])->name('auth.logout');

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
