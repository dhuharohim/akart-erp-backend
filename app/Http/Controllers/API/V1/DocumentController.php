<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Document::class);

        $filters = $request->only([
            'template_id',
            'category_id',
            'status',
            'search',
        ]);

        return DocumentResource::collection(
            $this->documents->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StoreDocumentRequest $request)
    {
        $templateId = $request->integer('template_id');
        if (!$templateId && $request->filled('template_key')) {
            $templateId = (int) $request->string('template_key')->toString();
        }
        $rawPayload = $request->input('payload');
        $payload = is_string($rawPayload)
            ? json_decode($rawPayload, true)
            : $rawPayload;
        if (!is_array($payload)) {
            $payload = null;
        }

        $document = $this->documents->createDocument([
            'template_id' => $templateId,
            'title' => $request->string('title')->toString(),
            'payload' => $payload,
            'status' => $request->input('status', 'draft'),
        ], $request->file('file'), $request->user()->id);

        return (new DocumentResource($document))->response()->setStatusCode(201);
    }

    public function templates(Request $request)
    {
        Gate::authorize('viewAny', Document::class);

        return response()->json([
            'data' => $this->documents->templateCatalog(),
        ]);
    }

    public function batchDownload(Request $request)
    {
        Gate::authorize('viewAny', Document::class);
        $validated = $request->validate([
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => ['integer', 'exists:documents,id'],
        ]);

        $signedUrl = $this->documents->batchDownload($validated['document_ids'], $request->user()->id);

        return response()->json(['signed_url' => $signedUrl]);
    }

    public function show(Document $document)
    {
        Gate::authorize('view', $document);
        $document->load(['template.category', 'attachments']);
        $attachment = $document->attachments->first();
        $signedUrl = $attachment
            ? $this->documents->signedAttachmentUrl($attachment)
            : null;
        if (!is_string($signedUrl) || trim($signedUrl) === '') {
            $signedUrl = null;
        }

        return response()->json([
            'data' => new DocumentResource($document),
            'signed_url' => $signedUrl,
            'rendered_html' => $signedUrl
                ? null
                : $this->documents->renderDocumentHtml($document),
        ]);
    }

    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);
        $document->delete();

        return response()->noContent();
    }
}
