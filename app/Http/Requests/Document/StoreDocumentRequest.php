<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('documents.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'template_id' => ['required_without:template_key', 'nullable', 'integer', 'exists:document_templates,id'],
            'template_key' => ['required_without:template_id', 'nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'payload' => ['nullable'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
