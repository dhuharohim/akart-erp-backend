<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'account_id' => $this->account_id,
            'amount' => $this->amount,
            'paid_at' => $this->paid_at,
            'method' => $this->method,
            'reference_number' => $this->reference_number,
            'invoice' => $this->whenLoaded('invoice', fn() => [
                'id' => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
            ]),
            'account' => $this->whenLoaded('account', fn() => [
                'id' => $this->account->id,
                'code' => $this->account->code,
                'name' => $this->account->name,
                'type' => $this->account->type,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
