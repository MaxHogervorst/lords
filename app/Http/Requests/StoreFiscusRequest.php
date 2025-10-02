<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFiscusRequest extends FormRequest
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
            'finalproductname' => 'required',
            'finalproductdescription' => 'required',
            'finalpriceperperson' => 'required|numeric',
            'finalselectedmembers' => 'min:1',
            'member' => 'required|array|min:1',
            'member.*' => 'integer|exists:members,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'finalproductname.required' => 'Product name is required',
            'finalproductdescription.required' => 'Product description is required',
            'finalpriceperperson.required' => 'Price per person is required',
            'finalpriceperperson.numeric' => 'Price must be a number',
            'member.required' => 'At least one member must be selected',
            'member.min' => 'At least one member must be selected',
        ];
    }
}
