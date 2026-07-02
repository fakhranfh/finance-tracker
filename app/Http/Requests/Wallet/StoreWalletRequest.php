<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-wallet');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'balance' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Wallet name is required',
            'name.max' => 'Wallet name must not exceed 255 characters',
            'balance.integer' => 'Starting balance must be a whole number',
            'balance.min' => 'Starting balance cannot be negative',
        ];
    }
}
