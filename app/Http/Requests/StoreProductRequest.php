<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
        // Support both store (name/productPrice) and update (productName/productPrice) field names
        return [
            'name' => 'sometimes|required|string|max:255',
            'productName' => 'sometimes|required|string|max:255',
            'productPrice' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'productName.required' => 'Product name is required',
            'productPrice.required' => 'Product price is required',
            'productPrice.numeric' => 'Price must be a valid number',
            'productPrice.min' => 'Price cannot be negative',
        ];
    }
}
