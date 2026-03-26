<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('invoices.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer'],
            'related_type' => ['nullable', 'in:purchase_order,event_series,event'],
            'related_id' => ['nullable', 'integer'],
            'invoice_number' => ['required', 'string', 'max:100', 'unique:invoices,invoice_number'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'in:draft,issued,paid,partial,overdue,cancelled'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'details' => ['sometimes', 'array'],
            'details.*.description' => ['required_with:details', 'string', 'max:255'],
            'details.*.unit' => ['nullable', 'string', 'max:50'],
            'details.*.quantity' => ['required_with:details', 'numeric', 'min:0.01'],
            'details.*.unit_price' => ['required_with:details', 'numeric', 'min:0'],
        ];
    }
}
