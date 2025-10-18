<?php

use App\Http\Requests\StoreFiscusRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Facades\Validator;

test('store fiscus request validates required fields', function () {
    $request = new StoreFiscusRequest();

    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('finalproductname'))->toBeTrue()
        ->and($validator->errors()->has('finalpriceperperson'))->toBeTrue()
        ->and($validator->errors()->has('member'))->toBeTrue();
});

test('store fiscus request validates member array', function () {
    $request = new StoreFiscusRequest();

    $data = [
        'finalproductname' => 'Test',
        'finalproductdescription' => 'Test',
        'finalpriceperperson' => 10,
        'member' => 'not-an-array',
    ];

    $validator = Validator::make($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('member'))->toBeTrue();
});

test('store group request validates required fields', function () {
    $request = new StoreGroupRequest();

    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue();
});

test('store member request validates required fields', function () {
    $request = new StoreMemberRequest();

    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->has('lastname'))->toBeTrue();
});

test('store order request validates numeric fields', function () {
    $request = new StoreOrderRequest();

    $data = [
        'memberId' => 'not-a-number',
        'product' => 'valid',
        'amount' => 'not-a-number',
    ];

    $validator = Validator::make($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('memberId'))->toBeTrue()
        ->and($validator->errors()->has('amount'))->toBeTrue();
});

test('store product request validates required fields', function () {
    $request = new StoreProductRequest();

    $validator = Validator::make([], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('productPrice'))->toBeTrue();
});
