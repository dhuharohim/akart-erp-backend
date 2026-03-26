<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;

class FinanceService
{
    public function recalculateProfit(Event $event): Event
    {
        $expenses = Expense::query()->where('event_id', $event->id)->sum('amount');
        $revenue = Invoice::query()->where('event_id', $event->id)->sum('total_amount');

        $event->update([
            'expense_amount' => $expenses,
            'revenue_amount' => $revenue,
            'profit_amount' => $revenue - $expenses,
        ]);

        return $event->refresh();
    }

    public function paymentSummary(): array
    {
        $totalPaid = Payment::query()->sum('amount');
        $invoiceTotal = Invoice::query()->sum('total_amount');

        return [
            'total_paid' => (float) $totalPaid,
            'invoice_total' => (float) $invoiceTotal,
            'outstanding' => (float) ($invoiceTotal - $totalPaid),
        ];
    }

    public function monthlyRevenue(): array
    {
        return Invoice::query()
            ->selectRaw("to_char(issue_date, 'YYYY-MM') as month, SUM(total_amount) as amount")
            ->whereNotNull('issue_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['month' => $row->month, 'amount' => (float) $row->amount])
            ->all();
    }
}
