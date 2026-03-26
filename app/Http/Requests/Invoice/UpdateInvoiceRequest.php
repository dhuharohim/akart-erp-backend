<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('invoices.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoiceId = (int) optional($this->route('invoice'))->id;

        return [
            'company_id' => ['sometimes', 'nullable', 'integer'],
            'related_type' => ['sometimes', 'nullable', 'in:purchase_order,event_series,event'],
            'related_id' => ['sometimes', 'nullable', 'integer'],
            'invoice_number' => ['sometimes', 'string', 'max:100', Rule::unique('invoices', 'invoice_number')->ignore($invoiceId)],
            'issue_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['sometimes', 'in:draft,issued,paid,partial,overdue,cancelled'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'details' => ['sometimes', 'array'],
            'details.*.description' => ['required_with:details', 'string', 'max:255'],
            'details.*.unit' => ['nullable', 'string', 'max:50'],
            'details.*.quantity' => ['required_with:details', 'numeric', 'min:0.01'],
            'details.*.unit_price' => ['required_with:details', 'numeric', 'min:0'],
        ];
    }
}
