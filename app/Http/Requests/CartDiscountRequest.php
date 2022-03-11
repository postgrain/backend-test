<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'userEmail' => ['required', 'email'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'uuid', 'distinct'],
            'products.*.categoryId' => ['required', 'uuid'],
            'products.*.quantity' => ['required', 'int', 'min:1'],
            'products.*.unitPrice' => ['required', 'string', 'regex:/^\d+\.\d{2}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'products.*.unitPrice.regex' => 'The :attribute must be a valid money.',
        ];
    }
}
