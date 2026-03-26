<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\EventSeriesSponsor;
use App\Models\EventTeamAssignment;
use App\Models\EventSeries;
use App\Models\EventSeriesContact;
use App\Models\EventStaff;
use App\Models\EventVendor;
use App\Models\Expense;
use App\Models\Journal;
use App\Models\Sponsor;
use App\Services\DocumentNumberService;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventSubResourceController extends Controller
{
    public function __construct(
        private FinanceService $finance,
        private DocumentNumberService $numberService,
    ) {}

    public function listStaff(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        return response()->json([
            'data' => $series->staff()->with('employee')->orderByDesc('created_at')->get()->map(fn($s) => [
                'id' => $s->id,
                'employee_id' => $s->employee_id,
                'employee_number' => $s->employee_number,
                'role_in_event' => $s->role_in_event,
                'attendance' => $s->attendance,
                'cost_amount' => $s->cost_amount,
                'employee' => $s->employee ? [
                    'id' => $s->employee->id,
                    'full_name' => $s->employee->full_name,
                    'position' => $s->employee->position,
                    'employee_type' => $s->employee->employee_type,
                ] : null,
            ]),
        ]);
    }

    public function addStaff(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'role_in_event' => 'nullable|string|max:255',
            'cost_amount' => 'nullable|numeric|min:0',
        ]);

        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);
        $employeeNumber = EventSeries::generateEmployeeNumber(
            $employee->employee_type ?? 'INT',
            $series->series_number ?? (string) $series->id,
            $series->id,
        );

        $staff = EventStaff::create([
            'event_id' => $series->event_id,
            'event_series_id' => $series->id,
            'employee_number' => $employeeNumber,
            ...$validated,
        ]);

        $staff->load('employee');

        return response()->json(['data' => [
            'id' => $staff->id,
            'employee_id' => $staff->employee_id,
            'employee_number' => $staff->employee_number,
            'role_in_event' => $staff->role_in_event,
            'attendance' => $staff->attendance,
            'cost_amount' => $staff->cost_amount,
            'employee' => $staff->employee ? [
                'id' => $staff->employee->id,
                'full_name' => $staff->employee->full_name,
                'position' => $staff->employee->position,
                'employee_type' => $staff->employee->employee_type,
            ] : null,
        ]], 201);
    }

    public function removeStaff(EventSeries $series, EventStaff $staff)
    {
        Gate::authorize('update', $series->event);
        abort_if($staff->event_series_id !== $series->id, 404);
        $staff->delete();
        return response()->noContent();
    }

    public function updateStaffAttendance(Request $request, EventSeries $series, EventStaff $staff)
    {
        Gate::authorize('update', $series->event);
        abort_if($staff->event_series_id !== $series->id, 404);
        $validated = $request->validate([
            'attendance' => 'required|boolean',
        ]);
        $staff->update($validated);
        return response()->json(['data' => $staff]);
    }

    public function listTeams(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        return response()->json([
            'data' => $series->teamAssignments()->with(['team', 'team.leadEmployee'])->orderByDesc('created_at')->get()->map(fn($assignment) => [
                'id' => $assignment->id,
                'team_id' => $assignment->team_id,
                'role_in_event' => $assignment->role_in_event,
                'attendance' => $assignment->attendance,
                'cost_amount' => $assignment->cost_amount,
                'team' => $assignment->team ? [
                    'id' => $assignment->team->id,
                    'name' => $assignment->team->name,
                    'team_code' => $assignment->team->team_code,
                    'lead_employee' => $assignment->team->leadEmployee ? [
                        'id' => $assignment->team->leadEmployee->id,
                        'full_name' => $assignment->team->leadEmployee->full_name,
                    ] : null,
                ] : null,
            ]),
        ]);
    }

    public function addTeam(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);
        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
            'role_in_event' => 'nullable|string|max:255',
            'cost_amount' => 'nullable|numeric|min:0',
        ]);

        $assignment = EventTeamAssignment::query()->updateOrCreate(
            [
                'event_series_id' => $series->id,
                'team_id' => $validated['team_id'],
            ],
            [
                'event_id' => $series->event_id,
                'role_in_event' => $validated['role_in_event'] ?? null,
                'cost_amount' => $validated['cost_amount'] ?? 0,
            ],
        );

        $assignment->load(['team', 'team.leadEmployee']);

        return response()->json(['data' => [
            'id' => $assignment->id,
            'team_id' => $assignment->team_id,
            'role_in_event' => $assignment->role_in_event,
            'attendance' => $assignment->attendance,
            'cost_amount' => $assignment->cost_amount,
            'team' => $assignment->team ? [
                'id' => $assignment->team->id,
                'name' => $assignment->team->name,
                'team_code' => $assignment->team->team_code,
                'lead_employee' => $assignment->team->leadEmployee ? [
                    'id' => $assignment->team->leadEmployee->id,
                    'full_name' => $assignment->team->leadEmployee->full_name,
                ] : null,
            ] : null,
        ]], 201);
    }

    public function removeTeam(EventSeries $series, EventTeamAssignment $assignment)
    {
        Gate::authorize('update', $series->event);
        abort_if($assignment->event_series_id !== $series->id, 404);
        $assignment->delete();

        return response()->noContent();
    }

    public function updateTeamAttendance(Request $request, EventSeries $series, EventTeamAssignment $assignment)
    {
        Gate::authorize('update', $series->event);
        abort_if($assignment->event_series_id !== $series->id, 404);
        $validated = $request->validate([
            'attendance' => 'required|boolean',
        ]);
        $assignment->update($validated);

        return response()->json(['data' => $assignment]);
    }

    public function listSponsors(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        return response()->json([
            'data' => $series->sponsors()->with('sponsor')->orderByDesc('created_at')->get()->map(fn($assignment) => [
                'id' => $assignment->id,
                'sponsor_id' => $assignment->sponsor_id,
                'contribution_amount' => $assignment->contribution_amount,
                'notes' => $assignment->notes,
                'term_conditions' => $assignment->term_conditions,
                'sponsor' => $assignment->sponsor ? [
                    'id' => $assignment->sponsor->id,
                    'name' => $assignment->sponsor->name,
                    'sponsor_code' => $assignment->sponsor->sponsor_code,
                    'email' => $assignment->sponsor->email,
                    'phone' => $assignment->sponsor->phone,
                ] : null,
            ]),
        ]);
    }

    public function addSponsor(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);
        $validated = $request->validate([
            'sponsor_id' => 'required|integer|exists:sponsors,id',
            'contribution_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'term_conditions' => 'nullable|string|max:5000',
        ]);

        $assignment = EventSeriesSponsor::query()->updateOrCreate(
            [
                'event_series_id' => $series->id,
                'sponsor_id' => $validated['sponsor_id'],
            ],
            [
                'event_id' => $series->event_id,
                'contribution_amount' => $validated['contribution_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'term_conditions' => $validated['term_conditions'] ?? null,
            ],
        );

        $assignment->load('sponsor');

        return response()->json(['data' => [
            'id' => $assignment->id,
            'sponsor_id' => $assignment->sponsor_id,
            'contribution_amount' => $assignment->contribution_amount,
            'notes' => $assignment->notes,
            'term_conditions' => $assignment->term_conditions,
            'sponsor' => $assignment->sponsor ? [
                'id' => $assignment->sponsor->id,
                'name' => $assignment->sponsor->name,
                'sponsor_code' => $assignment->sponsor->sponsor_code,
                'email' => $assignment->sponsor->email,
                'phone' => $assignment->sponsor->phone,
            ] : null,
        ]], 201);
    }

    public function removeSponsor(EventSeries $series, EventSeriesSponsor $assignment)
    {
        Gate::authorize('update', $series->event);
        abort_if($assignment->event_series_id !== $series->id, 404);
        $assignment->delete();

        return response()->noContent();
    }

    public function listVendors(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        return response()->json([
            'data' => $series->vendors()->with('vendor')->orderByDesc('created_at')->get()->map(fn($ev) => [
                'id' => $ev->id,
                'vendor_id' => $ev->vendor_id,
                'service_scope' => $ev->service_scope,
                'cost_amount' => $ev->cost_amount,
                'vendor' => $ev->vendor ? [
                    'id' => $ev->vendor->id,
                    'name' => $ev->vendor->name,
                    'email' => $ev->vendor->email,
                    'phone' => $ev->vendor->phone,
                ] : null,
            ]),
        ]);
    }

    public function addVendor(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);
        $validated = $request->validate([
            'vendor_id' => 'required|integer|exists:vendors,id',
            'service_scope' => 'nullable|string|max:500',
            'cost_amount' => 'nullable|numeric|min:0',
        ]);

        $eventVendor = EventVendor::create([
            'event_id' => $series->event_id,
            'event_series_id' => $series->id,
            ...$validated,
        ]);

        $eventVendor->load('vendor');

        return response()->json(['data' => [
            'id' => $eventVendor->id,
            'vendor_id' => $eventVendor->vendor_id,
            'service_scope' => $eventVendor->service_scope,
            'cost_amount' => $eventVendor->cost_amount,
            'vendor' => $eventVendor->vendor ? [
                'id' => $eventVendor->vendor->id,
                'name' => $eventVendor->vendor->name,
                'email' => $eventVendor->vendor->email,
                'phone' => $eventVendor->vendor->phone,
            ] : null,
        ]], 201);
    }

    public function removeVendor(EventSeries $series, EventVendor $eventVendor)
    {
        Gate::authorize('update', $series->event);
        abort_if($eventVendor->event_series_id !== $series->id, 404);
        $eventVendor->delete();
        return response()->noContent();
    }

    /* ── Budgets ─────────────────────────────────────── */

    public function listBudgets(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        return response()->json([
            'data' => $series->budgets()->with(['account', 'sponsor'])->orderByDesc('created_at')->get()->map(fn($b) => [
                'id' => $b->id,
                'account_id' => $b->account_id,
                'sponsor_id' => $b->sponsor_id,
                'total_amount' => $b->total_amount,
                'notes' => $b->notes,
                'created_at' => $b->created_at,
                'account' => $b->account ? [
                    'id' => $b->account->id,
                    'code' => $b->account->code,
                    'name' => $b->account->name,
                ] : null,
                'sponsor' => $b->sponsor ? [
                    'id' => $b->sponsor->id,
                    'name' => $b->sponsor->name,
                    'sponsor_code' => $b->sponsor->sponsor_code,
                ] : null,
            ]),
        ]);
    }

    public function addBudget(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);
        $validated = $request->validate([
            'account_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'sponsor_id' => 'nullable|integer|exists:sponsors,id',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $budget = Budget::create([
            'event_id' => $series->event_id,
            'event_series_id' => $series->id,
            ...$validated,
        ]);

        $budget->load(['account', 'sponsor']);
        $this->createBudgetJournal($series, $budget);

        return response()->json(['data' => [
            'id' => $budget->id,
            'account_id' => $budget->account_id,
            'sponsor_id' => $budget->sponsor_id,
            'total_amount' => $budget->total_amount,
            'notes' => $budget->notes,
            'created_at' => $budget->created_at,
            'account' => $budget->account ? [
                'id' => $budget->account->id,
                'code' => $budget->account->code,
                'name' => $budget->account->name,
            ] : null,
            'sponsor' => $budget->sponsor ? [
                'id' => $budget->sponsor->id,
                'name' => $budget->sponsor->name,
                'sponsor_code' => $budget->sponsor->sponsor_code,
            ] : null,
        ]], 201);
    }

    public function removeBudget(EventSeries $series, Budget $budget)
    {
        Gate::authorize('update', $series->event);
        abort_if($budget->event_series_id !== $series->id, 404);
        $budget->delete();
        return response()->noContent();
    }

    /* ── Expenses ────────────────────────────────────── */

    public function listExpenses(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        return response()->json([
            'data' => $series->expenses()->with('account')->orderByDesc('expense_date')->get()->map(fn($e) => [
                'id' => $e->id,
                'account_id' => $e->account_id,
                'description' => $e->description,
                'amount' => $e->amount,
                'expense_date' => $e->expense_date,
                'purchase_order_id' => $e->purchase_order_id,
                'created_at' => $e->created_at,
                'account' => $e->account ? [
                    'id' => $e->account->id,
                    'code' => $e->account->code,
                    'name' => $e->account->name,
                ] : null,
            ]),
        ]);
    }

    public function addExpense(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);
        $validated = $request->validate([
            'account_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'nullable|date',
            'purchase_order_id' => 'nullable|integer|exists:purchase_orders,id',
        ]);

        $expense = Expense::create([
            'event_id' => $series->event_id,
            'event_series_id' => $series->id,
            ...$validated,
        ]);

        $this->createExpenseJournal($series, $expense);
        $this->finance->recalculateProfit($series->event);
        $expense->load('account');

        return response()->json(['data' => [
            'id' => $expense->id,
            'account_id' => $expense->account_id,
            'description' => $expense->description,
            'amount' => $expense->amount,
            'expense_date' => $expense->expense_date,
            'purchase_order_id' => $expense->purchase_order_id,
            'created_at' => $expense->created_at,
            'account' => $expense->account ? [
                'id' => $expense->account->id,
                'code' => $expense->account->code,
                'name' => $expense->account->name,
            ] : null,
        ]], 201);
    }

    public function removeExpense(EventSeries $series, Expense $expense)
    {
        Gate::authorize('update', $series->event);
        abort_if($expense->event_series_id !== $series->id, 404);
        $expense->delete();
        $this->finance->recalculateProfit($series->event);
        return response()->noContent();
    }

    /* ── Analytics ────────────────────────────────────── */

    public function analytics(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        $totalBudget = $series->budgets()->sum('total_amount');
        $totalExpenses = $series->expenses()->sum('amount');
        $vendorCosts = $series->vendors()->sum('cost_amount');
        $staffCosts = $series->staff()->sum('cost_amount');
        $registrationCount = $series->registrations()->count();
        $registrationRevenue = $series->registrations()
            ->where('payment_status', 'paid')
            ->sum('amount');
        $utilization = $totalBudget > 0
            ? round(($totalExpenses / $totalBudget) * 100, 1)
            : 0;

        return response()->json(['data' => [
            'total_budget' => $totalBudget,
            'total_expenses' => $totalExpenses,
            'vendor_costs' => $vendorCosts,
            'staff_costs' => $staffCosts,
            'direct_expenses' => $totalExpenses,
            'budget_utilization' => $utilization,
            'registration_count' => $registrationCount,
            'registration_revenue' => $registrationRevenue,
            'net_profit' => $registrationRevenue - $totalExpenses - $vendorCosts - $staffCosts,
        ]]);
    }

    private function createBudgetJournal(EventSeries $series, Budget $budget): void
    {
        $reference = "BUDGET:{$budget->id}";
        if (Journal::query()->where('reference', $reference)->exists()) {
            return;
        }

        $debitAccount = ChartOfAccount::query()->where('type', 'cash_bank')->first()
            ?? ChartOfAccount::query()->where('type', 'asset')->first();
        $creditAccount = ChartOfAccount::query()->where('type', 'equity')->first()
            ?? ChartOfAccount::query()->where('type', 'liability')->first();

        if (!$debitAccount || !$creditAccount) {
            return;
        }

        $journal = Journal::query()->create([
            'journal_number' => $this->numberService->generate(Journal::class),
            'date' => now()->toDateString(),
            'description' => "Budget allocation for series {$series->series_number}",
            'reference' => $reference,
            'status' => 'posted',
            'total_debit' => $budget->total_amount,
            'total_credit' => $budget->total_amount,
        ]);

        $journal->lines()->create([
            'account_id' => $debitAccount->id,
            'description' => $budget->notes ?: "Budget #{$budget->id}",
            'debit' => $budget->total_amount,
            'credit' => 0,
        ]);
        $journal->lines()->create([
            'account_id' => $creditAccount->id,
            'description' => $budget->notes ?: "Budget #{$budget->id}",
            'debit' => 0,
            'credit' => $budget->total_amount,
        ]);
    }

    private function createExpenseJournal(EventSeries $series, Expense $expense): void
    {
        $reference = "EXPENSE:{$expense->id}";
        if (Journal::query()->where('reference', $reference)->exists()) {
            return;
        }

        $debitAccount = $expense->account_id
            ? ChartOfAccount::query()->find($expense->account_id)
            : ChartOfAccount::query()->where('type', 'expense')->first();
        $creditAccount = ChartOfAccount::query()->where('type', 'liability')->first();

        if (!$debitAccount || !$creditAccount) {
            return;
        }

        $journal = Journal::query()->create([
            'journal_number' => $this->numberService->generate(Journal::class),
            'date' => $expense->expense_date ?? now()->toDateString(),
            'description' => "Expense for series {$series->series_number}",
            'reference' => $reference,
            'status' => 'posted',
            'total_debit' => $expense->amount,
            'total_credit' => $expense->amount,
        ]);

        $journal->lines()->create([
            'account_id' => $debitAccount->id,
            'description' => $expense->description,
            'debit' => $expense->amount,
            'credit' => 0,
        ]);
        $journal->lines()->create([
            'account_id' => $creditAccount->id,
            'description' => $expense->description,
            'debit' => 0,
            'credit' => $expense->amount,
        ]);
    }

    // --- Contact Persons ---

    public function listContacts(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        return response()->json([
            'data' => $series->contacts()
                ->with('staff.employee')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'event_staff_id' => $c->event_staff_id,
                    'label' => $c->label,
                    'sort_order' => $c->sort_order,
                    'employee_name' => $c->staff?->employee?->full_name,
                    'employee_number' => $c->staff?->employee_number,
                    'phone' => $c->staff?->employee?->phone,
                    'email' => $c->staff?->employee?->email,
                    'role_in_event' => $c->staff?->role_in_event,
                ]),
        ]);
    }

    public function addContact(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);

        $validated = $request->validate([
            'event_staff_id' => 'required|integer|exists:event_staff,id',
            'label' => 'nullable|string|max:255',
        ]);

        $staff = EventStaff::findOrFail($validated['event_staff_id']);
        abort_if($staff->event_series_id !== $series->id, 422, 'Staff not assigned to this series.');

        $contact = EventSeriesContact::firstOrCreate(
            [
                'event_series_id' => $series->id,
                'event_staff_id' => $validated['event_staff_id'],
            ],
            [
                'label' => $validated['label'] ?? null,
                'sort_order' => $series->contacts()->count(),
            ],
        );

        return response()->json(['data' => $contact], 201);
    }

    public function updateContact(Request $request, EventSeries $series, EventSeriesContact $contact)
    {
        Gate::authorize('update', $series->event);
        abort_if($contact->event_series_id !== $series->id, 404);

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $contact->update($validated);

        return response()->json(['data' => $contact]);
    }

    public function removeContact(EventSeries $series, EventSeriesContact $contact)
    {
        Gate::authorize('update', $series->event);
        abort_if($contact->event_series_id !== $series->id, 404);
        $contact->delete();

        return response()->noContent();
    }
}
