<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('payments.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_at' => ['nullable', 'date'],
            'method' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $accountId = $this->input('account_id');
            if (!$accountId) {
                return;
            }
            $account = \App\Models\ChartOfAccount::query()->find($accountId);
            if (!$account || $account->type !== 'cash_bank') {
                $validator->errors()->add('account_id', 'Selected account must be cash/bank type.');
            }
        });
    }
}
