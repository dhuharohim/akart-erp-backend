<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $payment)
    {
        parent::__construct($payment);
    }

    public function paginate(int $perPage = 15)
    {
        return $this->query()->with(['invoice', 'account'])->latest()->paginate($perPage);
    }
}
