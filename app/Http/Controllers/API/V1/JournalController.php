<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journal\StoreJournalRequest;
use App\Http\Requests\Journal\UpdateJournalRequest;
use App\Http\Resources\JournalResource;
use App\Models\Journal;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JournalController extends Controller
{
    public function __construct(private JournalService $journals) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Journal::class);

        $filters = $request->only(['journal_number', 'status', 'reference']);

        return JournalResource::collection(
            $this->journals->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StoreJournalRequest $request)
    {
        $journal = $this->journals->create($request->validated());

        return (new JournalResource($journal))->response()->setStatusCode(201);
    }

    public function show(Journal $journal)
    {
        Gate::authorize('view', $journal);

        return new JournalResource($journal->load('lines.account'));
    }

    public function update(UpdateJournalRequest $request, Journal $journal)
    {
        return new JournalResource($this->journals->update($journal, $request->validated()));
    }

    public function destroy(Journal $journal)
    {
        Gate::authorize('delete', $journal);
        $this->journals->delete($journal);

        return response()->noContent();
    }
}
