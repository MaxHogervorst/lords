<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckBillRequest extends FormRequest
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
            'name' => 'required|string',
            'iban' => 'required|string',
            'invoiceGroup' => 'required|integer|exists:invoice_groups,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Last name is required',
            'iban.required' => 'IBAN is required',
            'invoiceGroup.required' => 'Invoice month is required',
            'invoiceGroup.exists' => 'Selected invoice month does not exist',
        ];
    }
}
