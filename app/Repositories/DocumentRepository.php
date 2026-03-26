<?php

namespace App\Repositories;

use App\Models\Document;

class DocumentRepository extends BaseRepository
{
    public function __construct(Document $document)
    {
        parent::__construct($document);
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->query()->with(['template.category', 'attachments']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['template_id'])) {
            $query->where('template_id', $filters['template_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->whereHas('template', fn($templateQuery) => $templateQuery->where('category_id', $filters['category_id']));
        }
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhereHas('template', fn($templateQuery) => $templateQuery->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($perPage);
    }
}
