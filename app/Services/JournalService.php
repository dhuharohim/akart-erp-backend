<?php

namespace App\Services;

use App\Models\Journal;
use App\Repositories\JournalRepository;
use Illuminate\Support\Facades\DB;

class JournalService
{
    public function __construct(
        private JournalRepository $journals,
        private DocumentNumberService $numberService,
    ) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->journals->paginate($perPage, $filters);
    }

    public function create(array $data): Journal
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['journal_number'] = $this->numberService->generate(Journal::class);
            $data['total_debit'] = collect($lines)->sum('debit');
            $data['total_credit'] = collect($lines)->sum('credit');

            $journal = $this->journals->create($data);

            foreach ($lines as $line) {
                $journal->lines()->create($line);
            }

            return $journal->load('lines.account');
        });
    }

    public function update(Journal $journal, array $data): Journal
    {
        return DB::transaction(function () use ($journal, $data) {
            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            if ($lines !== null) {
                $data['total_debit'] = collect($lines)->sum('debit');
                $data['total_credit'] = collect($lines)->sum('credit');
            }

            $journal = $this->journals->update($journal, $data);

            if ($lines !== null) {
                $journal->lines()->forceDelete();
                foreach ($lines as $line) {
                    $journal->lines()->create($line);
                }
            }

            return $journal->load('lines.account');
        });
    }

    public function delete(Journal $journal): bool
    {
        return DB::transaction(fn () => $this->journals->delete($journal));
    }
}
