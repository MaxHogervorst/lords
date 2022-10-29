<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FiscusController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SepaController;
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

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

// require __DIR__.'/auth.php';

Route::post('invoice/setperson', [InvoiceController::class, 'postSetPerson']);
Route::post('invoice/setpersonalinvoicegroup', [InvoiceController::class, 'postSetPersonalInvoiceGroup']);
Route::get('check-bill', [InvoiceController::class, 'getPerPerson']);
Route::get('auth/login', [AuthController::class, 'getLogin'])->name('login');
Route::get('auth/logout', [AuthController::class, 'getLogout']);
Route::post('auth/authenticate', [AuthController::class, 'postAuthenticate']);
Route::group(['middleware' => ['authAdmin']], function () {
    Route::get('sepa/download/{file_name}', [SepaController::class, 'downloadFile']);
    Route::resource('sepa', SepaController::class, ['only' => ['index', 'store']]);
    Route::get('fiscus/invoiceprices/{id}', [FiscusController::class, 'getInvoiceprices']);
    Route::get('fiscus/allinvoicelines/{id}', [FiscusController::class, 'getAllinvoicelines']);
    Route::get('fiscus/specificinvoicelines/{id}', [FiscusController::class, 'getSpecificinvoicelines']);
    Route::get('fiscus/edit', [FiscusController::class, 'getEdit']);
    Route::resource('fiscus', FiscusController::class, ['except' => ['edit']]);
    Route::get('invoice', [InvoiceController::class, 'getIndex']);
    Route::get('invoice/pdf', [InvoiceController::class, 'getPdf']);
    Route::get('invoice/excel', [InvoiceController::class, 'getExcel']);
    Route::get('invoice/sepa', [InvoiceController::class, 'getSepa']);
    Route::post('invoice/storeinvoicegroup', [InvoiceController::class, 'postStoreinvoicegroup']);
    Route::post('invoice/selectinvoicegroup', [InvoiceController::class, 'postSelectinvoicegroup']);
});
Route::group(['middleware' => 'auth'], function () {
    Route::post('order/store/{type}', [OrderController::class, 'postStore']);
    Route::post('group/addmember', [GroupController::class, 'postAddMember']);
    Route::get('group/deletegroupmember/{id}', [GroupController::class, 'getDeletegroupmember']);
    Route::resource('group', GroupController::class);
    Route::resource('product', ProductController::class);
    Route::resource('member', MemberController::class);
    Route::get('/', [HomeController::class, 'getIndex']);
});
