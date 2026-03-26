<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentCategory;
use App\Models\DocumentTemplate;
use App\Repositories\DocumentRepository;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class DocumentService
{
    public function __construct(private DocumentRepository $documents) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->documents->paginate($perPage, $filters);
    }

    public function templateCatalog(): array
    {
        $categories = DocumentCategory::query()->orderBy('name')->get();
        $templates = [];
        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[strtolower($category->name)] = $category->name;
            foreach ($category->templates()->orderBy('name')->get() as $template) {
                $formSchema = is_array($template->form_schema) ? $template->form_schema : [];
                $required = array_values(array_filter($formSchema['fields'] ?? [], fn($field) => ($field['required'] ?? false) === true));
                $templates[] = [
                    'id' => $template->id,
                    'key' => (string) $template->id,
                    'title' => $template->name,
                    'category' => strtolower($category->name),
                    'category_label' => $category->name,
                    'required_fields' => array_map(fn($field) => (string) ($field['name'] ?? ''), $required),
                    'form_schema' => $formSchema,
                    'template_layout' => $template->template_layout,
                ];
            }
        }

        return [
            'status' => 'pra',
            'templates' => $templates,
            'categories' => $categoryMap,
            'can_create' => count($templates) > 0,
        ];
    }

    public function createDocument(array $data, ?UploadedFile $file, int $uploadedBy): Document
    {
        $template = DocumentTemplate::query()->findOrFail($data['template_id']);
        $document = $this->documents->create([
            'template_id' => $data['template_id'],
            'title' => $data['title'] ?: $template->name,
            'payload' => $data['payload'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ]);

        if ($file) {
            $diskName = $this->defaultDiskName();
            $path = Storage::disk($diskName)->putFile('documents/uploads', $file, ['visibility' => 'private']);
            $document->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'disk' => $diskName,
                'uploaded_by' => $uploadedBy,
            ]);
        }

        return $document->load(['template.category', 'attachments']);
    }

    public function batchDownload(array $documentIds, int $userId): string
    {
        $documents = Document::query()->with('attachments')->whereIn('id', $documentIds)->get();
        if ($documents->count() === 0) {
            abort(422, 'No documents selected');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'docs_zip_');
        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($documents as $document) {
            foreach ($document->attachments as $attachment) {
                $disk = $this->disk($attachment->disk);
                if (!$disk->exists($attachment->file_path)) {
                    continue;
                }
                $stream = $disk->readStream($attachment->file_path);
                if (!$stream) {
                    continue;
                }
                $content = stream_get_contents($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                if ($content === false) {
                    continue;
                }
                $zip->addFromString($attachment->file_name, $content);
            }
        }
        $zip->close();
        $zipContent = file_get_contents($tmpFile) ?: '';
        @unlink($tmpFile);

        $diskName = $this->defaultDiskName();
        $batchName = 'documents/batch/' . Str::uuid()->toString() . '.zip';
        Storage::disk($diskName)->put($batchName, $zipContent, [
            'visibility' => 'private',
            'ContentType' => 'application/zip',
        ]);

        $attachment = new DocumentAttachment([
            'disk' => $diskName,
            'file_path' => $batchName,
        ]);

        return $this->signedAttachmentUrl($attachment, 10);
    }

    public function signedAttachmentUrl(DocumentAttachment $attachment, int $minutes = 10): string
    {
        $diskName = $attachment->disk;
        if ($diskName === 'r2' && !$this->isR2Configured()) {
            return '';
        }
        $disk = $this->disk($diskName);
        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl($attachment->file_path, now()->addMinutes($minutes));
        }

        return $disk->url($attachment->file_path);
    }

    public function renderDocumentHtml(Document $document): string
    {
        $document->loadMissing('template');
        $template = $document->template;
        if (!$template) {
            return '<html><body><h1>Document</h1></body></html>';
        }

        $payload = is_array($document->payload) ? $document->payload : [];
        $layout = (string) ($template->template_layout ?? '');
        $rendered = $layout;
        foreach ($payload as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $rendered = str_replace('{{' . $key . '}}', (string) $value, $rendered);
        }
        if ($rendered === '') {
            $rendered = '<html><body><h1>' . ($document->title ?: $template->name) . '</h1></body></html>';
        }

        return $rendered;
    }

    private function disk(string $name): FilesystemAdapter
    {
        return Storage::disk($name);
    }

    private function defaultDiskName(): string
    {
        return $this->isR2Configured() ? 'r2' : 'public';
    }

    private function isR2Configured(): bool
    {
        $bucket = config('filesystems.disks.r2.bucket');

        return is_string($bucket) && trim($bucket) !== '';
    }
}
