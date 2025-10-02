<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
// echo 'Here i am';
// exit;
Route::post('invoice/setperson', 'InvoiceController@postSetPerson');
Route::post('invoice/setpersonalinvoicegroup', 'InvoiceController@postSetPersonalInvoiceGroup');
Route::get('check-bill', 'InvoiceController@getPerPerson');
Route::get('auth/login', 'AuthController@getLogin');
Route::get('auth/logout', 'AuthController@getLogout');
Route::post('auth/authenticate', 'AuthController@postAuthenticate');
Route::group(['middleware' => ['auth', 'admin']], function () {
    Route::get('downloadSEPA/{filename}', function ($filename) {
        // Check if file exists in app/storage/file folder
        $file_path = storage_path().'/SEPA/'.$filename;
        if (file_exists($file_path)) {
            // Send Download
            return Response::download($file_path, $filename, [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            // Error
            exit('Requested file does not exist on our server!');
        }
    });

    Route::resource('sepa', 'SepaController', ['only' => ['index', 'store']]);
    Route::get('fiscus/invoiceprices/{id}', 'FiscusController@getInvoiceprices');
    Route::get('fiscus/allinvoicelines/{id}', 'FiscusController@getAllinvoicelines');
    Route::get('fiscus/specificinvoicelines/{id}', 'FiscusController@getSpecificinvoicelines');
    Route::get('fiscus/edit', 'FiscusController@getEdit');
    Route::resource('fiscus', 'FiscusController', ['except' => ['edit']]);
    Route::get('invoice', 'InvoiceController@getIndex');
    Route::get('invoice/pdf', 'InvoiceController@getPdf');
    Route::get('invoice/excel', 'InvoiceController@getExcel');
    Route::get('invoice/sepa', 'InvoiceController@getSepa');
    Route::post('invoice/storeinvoicegroup', 'InvoiceController@postStoreinvoicegroup');
    Route::post('invoice/selectinvoicegroup', 'InvoiceController@postSelectinvoicegroup');
});
Route::group(['middleware' => 'auth'], function () {
    Route::post('order/store/{type}', 'OrderController@postStore');
    Route::post('group/addmember', 'GroupController@postAddMember');
    Route::get('group/deletegroupmember/{id}', 'GroupController@getDeletegroupmember');
    Route::resource('group', 'GroupController');
    Route::resource('product', 'ProductController');
    Route::resource('member', 'MemberController');
    Route::get('/', 'HomeController@getIndex');
});

// Route::get('createuser', function()
// {
//
//	$role = Sentinel::getRoleRepository()->createModel()->create([
//		'name' => 'Lord',
//		'slug' => 'lord',
//	]);
//
//	$credentials = [
//		'email'    => 'lord',
//		'password' => 'lordsgeil',
//	];
//
//	$user = Sentinel::create($credentials);
//
//	$role->users()->attach($user);
//
//	$role = Sentinel::getRoleRepository()->createModel()->create([
//		'name' => 'Admin',
//		'slug' => 'admin',
//	]);
//
//	$credentials = [
//		'email'    => 'fiscus',
//		'password' => 'geldgeld',
//	];
//
//	$user = Sentinel::create($credentials);
//
//	$role->users()->attach($user);
//	$credentials = [
//		'email'    => 'lotm',
//		'password' => 'zuipenmooi',
//	];
//
//	$user = Sentinel::create($credentials);
//
//	$role->users()->attach($user);
// });
