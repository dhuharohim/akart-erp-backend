<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_id' => $this->template_id,
            'title' => $this->title,
            'status' => $this->status,
            'status_scope' => $this->status,
            'payload' => $this->payload,
            'version_number' => 1,
            'file_name' => $this->attachments->first()?->file_name,
            'file_type' => $this->attachments->first()?->file_type,
            'template' => $this->whenLoaded('template', fn() => [
                'id' => $this->template->id,
                'name' => $this->template->name,
                'type' => $this->template->type,
                'form_schema' => $this->template->form_schema,
                'template_layout' => $this->template->template_layout,
                'category' => $this->template->relationLoaded('category') && $this->template->category ? [
                    'id' => $this->template->category->id,
                    'name' => $this->template->category->name,
                ] : null,
            ]),
            'attachments' => $this->whenLoaded('attachments', fn() => $this->attachments->map(fn($attachment) => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path,
                'file_type' => $attachment->file_type,
                'disk' => $attachment->disk,
                'uploaded_by' => $attachment->uploaded_by,
                'created_at' => $attachment->created_at,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
