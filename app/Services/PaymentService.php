<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PaymentRepository $payments,
        private DocumentNumberService $numberService,
    ) {}

    public function paginate(int $perPage = 15)
    {
        return $this->payments->paginate($perPage);
    }

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = $this->payments->create($data);
            $payment->load('invoice');

            if ($payment->invoice) {
                $invoice = $payment->invoice;
                $paidAmount = Payment::query()->where('invoice_id', $invoice->id)->sum('amount');
                $newStatus = $paidAmount >= (float) $invoice->total_amount ? 'paid' : 'partial';
                Invoice::query()->whereKey($invoice->id)->update(['status' => $newStatus]);

                $payableAccount = ChartOfAccount::query()
                    ->where('type', 'liability')
                    ->where(function ($query) {
                        $query->whereRaw('LOWER(name) LIKE ?', ['%payable%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%account payable%']);
                    })
                    ->first() ?? ChartOfAccount::query()->where('type', 'liability')->first();
                $cashAccount = $payment->account_id
                    ? ChartOfAccount::query()->find($payment->account_id)
                    : null;

                if ($payableAccount && $cashAccount) {
                    $reference = "PAYMENT:{$payment->reference_number}";
                    $exists = Journal::query()->where('reference', $reference)->exists();
                    if (!$exists) {
                        $journal = Journal::query()->create([
                            'journal_number' => $this->numberService->generate(Journal::class),
                            'date' => $payment->paid_at?->toDateString() ?? now()->toDateString(),
                            'description' => "Payment {$payment->reference_number}",
                            'reference' => $reference,
                            'status' => 'posted',
                            'total_debit' => $payment->amount,
                            'total_credit' => $payment->amount,
                        ]);

                        $journal->lines()->create([
                            'account_id' => $payableAccount->id,
                            'description' => "Invoice {$invoice->invoice_number}",
                            'debit' => $payment->amount,
                            'credit' => 0,
                        ]);

                        $journal->lines()->create([
                            'account_id' => $cashAccount->id,
                            'description' => "Invoice {$invoice->invoice_number}",
                            'debit' => 0,
                            'credit' => $payment->amount,
                        ]);
                    }
                }

                $purchaseOrder = $invoice->related_type === 'purchase_order'
                    ? PurchaseOrder::query()->find($invoice->related_id)
                    : null;
                if ($purchaseOrder && $purchaseOrder->procurement_type === 'event' && $newStatus === 'paid') {
                    $expenseAccount = ChartOfAccount::query()->where('type', 'expense')->first();
                    $description = "Paid invoice {$invoice->invoice_number} for {$purchaseOrder->po_number}";

                    Expense::query()->firstOrCreate([
                        'purchase_order_id' => $purchaseOrder->id,
                        'description' => $description,
                    ], [
                        'event_id' => $purchaseOrder->event_id,
                        'event_series_id' => $purchaseOrder->event_series_id,
                        'account_id' => $expenseAccount?->id,
                        'purchase_order_id' => $purchaseOrder->id,
                        'description' => $description,
                        'amount' => $invoice->total_amount,
                        'expense_date' => $payment->paid_at?->toDateString() ?? now()->toDateString(),
                    ]);
                }
            }

            return $payment;
        });
    }

    public function update(Payment $payment, array $data): Payment
    {
        return DB::transaction(fn() => $this->payments->update($payment, $data));
    }

    public function delete(Payment $payment): bool
    {
        return DB::transaction(fn() => $this->payments->delete($payment));
    }
}
