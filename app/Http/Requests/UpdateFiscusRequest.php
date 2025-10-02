<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFiscusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'finalproductdescription' => 'required',
            'finalpriceperperson' => 'required|numeric',
            'member' => 'required|array|min:1',
            'member.*' => 'integer|exists:members,id',
            'isupdate' => 'sometimes|integer|exists:invoice_product_prices,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'finalproductdescription.required' => 'Product description is required',
            'finalpriceperperson.required' => 'Price per person is required',
            'finalpriceperperson.numeric' => 'Price must be a number',
            'member.required' => 'At least one member must be selected',
            'member.min' => 'At least one member must be selected',
        ];
    }
}
