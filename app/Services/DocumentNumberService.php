<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Prefix mapping: model class => prefix string.
     */
    private const PREFIXES = [
        \App\Models\Event::class              => 'EV',
        \App\Models\EventSeries::class        => 'SER',
        \App\Models\PurchaseOrder::class      => 'PO',
        \App\Models\Invoice::class            => 'INV',
        \App\Models\Payment::class            => 'PAY',
        \App\Models\Journal::class            => 'JRN',
        \App\Models\EventRegistration::class  => 'REG',
        \App\Models\Venue::class              => 'VN',
    ];

    /**
     * Column mapping: model class => number column name.
     */
    private const COLUMNS = [
        \App\Models\Event::class              => 'event_number',
        \App\Models\EventSeries::class        => 'series_number',
        \App\Models\PurchaseOrder::class       => 'po_number',
        \App\Models\Invoice::class             => 'invoice_number',
        \App\Models\Payment::class             => 'reference_number',
        \App\Models\Journal::class             => 'journal_number',
        \App\Models\EventRegistration::class   => 'registration_number',
        \App\Models\Venue::class               => 'code',
    ];

    /**
     * Generate the next sequential number for a given model.
     *
     * Format: PREFIX-YYYYMM-001
     *
     * Uses a database lock to prevent race conditions.
     */
    public function generate(string $modelClass): string
    {
        $prefix = self::PREFIXES[$modelClass]
            ?? throw new \InvalidArgumentException("No prefix configured for {$modelClass}");

        $column = self::COLUMNS[$modelClass];
        $table  = (new $modelClass)->getTable();
        $yearMonth = now()->format('Ym');
        $pattern = "{$prefix}-{$yearMonth}-%";

        $lastNumber = DB::table($table)
            ->where($column, 'like', $pattern)
            ->lockForUpdate()
            ->max($column);

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, -3);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $yearMonth, $nextSequence);
    }
}
