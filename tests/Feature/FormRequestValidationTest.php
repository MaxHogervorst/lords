<?php

namespace Tests\Feature;

use App\Http\Requests\StoreFiscusRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_fiscus_request_validates_required_fields(): void
    {
        $request = new StoreFiscusRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('finalproductname'));
        $this->assertTrue($validator->errors()->has('finalproductdescription'));
        $this->assertTrue($validator->errors()->has('finalpriceperperson'));
        $this->assertTrue($validator->errors()->has('member'));
    }

    public function test_store_fiscus_request_validates_member_array(): void
    {
        $request = new StoreFiscusRequest;

        $data = [
            'finalproductname' => 'Test',
            'finalproductdescription' => 'Test',
            'finalpriceperperson' => 10,
            'member' => 'not-an-array',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('member'));
    }

    public function test_store_group_request_validates_required_fields(): void
    {
        $request = new StoreGroupRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
        $this->assertTrue($validator->errors()->has('groupDate'));
    }

    public function test_store_member_request_validates_required_fields(): void
    {
        $request = new StoreMemberRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
        $this->assertTrue($validator->errors()->has('lastname'));
    }

    public function test_store_order_request_validates_numeric_fields(): void
    {
        $request = new StoreOrderRequest;

        $data = [
            'memberId' => 'not-a-number',
            'product' => 'valid',
            'amount' => 'not-a-number',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('memberId'));
        $this->assertTrue($validator->errors()->has('amount'));
    }

    public function test_store_product_request_validates_required_fields(): void
    {
        $request = new StoreProductRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
        $this->assertTrue($validator->errors()->has('productPrice'));
    }
}
