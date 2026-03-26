<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('purchase-orders.create') ?? false;
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
            'procurement_type' => ['required', 'in:event,asset'],
            'po_date' => ['nullable', 'date'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'event_series_id' => ['nullable', 'integer', 'exists:event_series,id', 'required_if:procurement_type,event'],
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'po_number' => ['prohibited'],
            'status' => ['required', 'in:draft,pending,approved,sent,completed,cancelled'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.vendor_item_id' => ['nullable', 'integer', 'exists:vendor_items,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
