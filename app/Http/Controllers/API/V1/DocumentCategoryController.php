<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->can('documents.view'), 403);

        return response()->json([
            'data' => DocumentCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->can('documents.create'), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:document_categories,name'],
        ]);

        $category = DocumentCategory::query()->create($validated);

        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, DocumentCategory $documentCategory)
    {
        abort_unless($request->user()?->can('documents.update'), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:document_categories,name,'.$documentCategory->id],
        ]);

        $documentCategory->update($validated);

        return response()->json(['data' => $documentCategory]);
    }

    public function destroy(Request $request, DocumentCategory $documentCategory)
    {
        abort_unless($request->user()?->can('documents.delete'), 403);
        $documentCategory->delete();

        return response()->noContent();
    }
}
