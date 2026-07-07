<?php

namespace App\Http\Requests\Transaction;

use App\Http\Requests\Concerns\ConvertsClientTimezone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    use ConvertsClientTimezone;

    public function authorize(): bool
    {
        if ($this->input('type') === 'transfer') {
            return $this->user()->can('create-transfer');
        }

        return $this->user()->can('create-transaction');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:income,expense,transfer'],
            'wallet_id' => ['required', 'uuid', Rule::exists('wallets', 'id')->where('user_id', $this->user()->id)],
            'category_id' => ['required_if:type,income,expense', 'nullable', 'uuid', Rule::exists('categories', 'id')->where(function ($query) {
                $query->where(fn ($q) => $q->where('user_id', $this->user()->id)->orWhereNull('user_id'));

                if (in_array($this->input('type'), ['income', 'expense'], true)) {
                    $query->where('type', $this->input('type'));
                }
            })],
            'to_wallet_id' => ['required_if:type,transfer', 'nullable', 'uuid', 'different:wallet_id', Rule::exists('wallets', 'id')->where('user_id', $this->user()->id)],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'transaction_date' => ['required', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Convert the client-local transaction date/time into the application's
     * timezone before validation, since the form submits a naive datetime
     * string in the browser's local timezone.
     */
    protected function prepareForValidation(): void
    {
        $this->convertFieldsToAppTimezone(['transaction_date']);
    }

    public function messages(): array
    {
        return [
            'wallet_id.required' => 'Please select a wallet',
            'wallet_id.exists' => 'The selected wallet is invalid',
            'category_id.required_if' => 'Please select a category',
            'category_id.exists' => 'The selected category is invalid or does not match the transaction type',
            'to_wallet_id.required_if' => 'Please select a destination wallet',
            'to_wallet_id.different' => 'Destination wallet must be different from the source wallet',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than zero',
            'amount.max' => 'Amount is too large',
            'transaction_date.required' => 'Date is required',
            'transaction_date.before_or_equal' => 'Date cannot be in the future',
        ];
    }
}
