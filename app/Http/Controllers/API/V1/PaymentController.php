<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\ChartOfAccount;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function index()
    {
        Gate::authorize('viewAny', Payment::class);

        return PaymentResource::collection($this->payments->paginate());
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = $this->payments->create($request->validated());

        return (new PaymentResource($payment->load(['invoice', 'account'])))->response()->setStatusCode(201);
    }

    public function show(Payment $payment)
    {
        Gate::authorize('view', $payment);

        return new PaymentResource($payment->load(['invoice', 'account']));
    }

    public function update(Request $request, Payment $payment)
    {
        Gate::authorize('update', $payment);
        $validated = $request->validate([
            'account_id' => ['sometimes', 'integer', 'exists:chart_of_accounts,id'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'paid_at' => ['sometimes', 'nullable', 'date'],
            'method' => ['sometimes', 'nullable', 'string', 'max:100'],
            'reference_number' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if (array_key_exists('account_id', $validated)) {
            $account = ChartOfAccount::query()->find($validated['account_id']);
            if (!$account || $account->type !== 'cash_bank') {
                return response()->json([
                    'message' => 'The selected account must be cash/bank type.',
                    'errors' => ['account_id' => ['The selected account must be cash/bank type.']],
                ], 422);
            }
        }

        return new PaymentResource($this->payments->update($payment, $validated)->load(['invoice', 'account']));
    }

    public function destroy(Payment $payment)
    {
        Gate::authorize('delete', $payment);
        $this->payments->delete($payment);

        return response()->noContent();
    }
}
