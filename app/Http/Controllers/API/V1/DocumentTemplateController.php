<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->can('documents.view'), 403);

        return response()->json([
            'data' => DocumentTemplate::query()->with('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->can('documents.create'), 403);
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:document_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text_based,table_based,mixed'],
            'form_schema' => ['nullable', 'array'],
            'template_layout' => ['nullable', 'string'],
        ]);

        $template = DocumentTemplate::query()->create($validated);

        return response()->json(['data' => $template->load('category')], 201);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        abort_unless($request->user()?->can('documents.update'), 403);
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:document_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text_based,table_based,mixed'],
            'form_schema' => ['nullable', 'array'],
            'template_layout' => ['nullable', 'string'],
        ]);

        $documentTemplate->update($validated);

        return response()->json(['data' => $documentTemplate->load('category')]);
    }

    public function destroy(Request $request, DocumentTemplate $documentTemplate)
    {
        abort_unless($request->user()?->can('documents.delete'), 403);
        $documentTemplate->delete();

        return response()->noContent();
    }
}
