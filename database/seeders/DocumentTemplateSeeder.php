<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('document_categories') || !Schema::hasTable('document_templates')) {
            return;
        }

        $categories = [
            'Legal',
            'Operational',
            'Permits',
        ];

        $categoryMap = [];
        foreach ($categories as $name) {
            $category = DocumentCategory::query()->updateOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
            $categoryMap[$name] = $category->id;
        }

        $templates = [
            [
                'category' => 'Permits',
                'name' => 'Surat Izin Keramaian',
                'type' => 'text_based',
                'form_schema' => [
                    'fields' => [
                        ['name' => 'event_name', 'type' => 'text', 'required' => true],
                        ['name' => 'series_name', 'type' => 'text', 'required' => true],
                        ['name' => 'venue_name', 'type' => 'text', 'required' => true],
                        ['name' => 'start_date', 'type' => 'date', 'required' => true],
                        ['name' => 'end_date', 'type' => 'date', 'required' => true],
                    ],
                ],
                'template_layout' => '<h1>Surat Izin Keramaian</h1><p>{{event_name}}</p>',
            ],
            [
                'category' => 'Legal',
                'name' => 'Surat Perintah Kerja',
                'type' => 'mixed',
                'form_schema' => [
                    'fields' => [
                        ['name' => 'event_name', 'type' => 'text', 'required' => true],
                        ['name' => 'vendor_name', 'type' => 'text', 'required' => true],
                        ['name' => 'scope_of_work', 'type' => 'textarea', 'required' => true],
                        ['name' => 'contract_value', 'type' => 'number', 'required' => true],
                    ],
                ],
                'template_layout' => '<h1>Surat Perintah Kerja</h1><p>{{vendor_name}}</p>',
            ],
            [
                'category' => 'Operational',
                'name' => 'Daftar Tamu',
                'type' => 'table_based',
                'form_schema' => [
                    'fields' => [
                        ['name' => 'event_name', 'type' => 'text', 'required' => true],
                        ['name' => 'series_name', 'type' => 'text', 'required' => true],
                        ['name' => 'guest_table', 'type' => 'table', 'required' => true],
                    ],
                ],
                'template_layout' => '<h1>Daftar Tamu</h1><table>{{guest_table}}</table>',
            ],
            [
                'category' => 'Operational',
                'name' => 'Cue Card MC',
                'type' => 'mixed',
                'form_schema' => [
                    'fields' => [
                        ['name' => 'event_name', 'type' => 'text', 'required' => true],
                        ['name' => 'time_schedule', 'type' => 'textarea', 'required' => true],
                        ['name' => 'notes', 'type' => 'textarea', 'required' => false],
                    ],
                ],
                'template_layout' => '<h1>Cue Card MC</h1><p>{{time_schedule}}</p>',
            ],
            [
                'category' => 'Legal',
                'name' => 'Surat Kuasa',
                'type' => 'text_based',
                'form_schema' => [
                    'fields' => [
                        ['name' => 'grantor_name', 'type' => 'text', 'required' => true],
                        ['name' => 'receiver_name', 'type' => 'text', 'required' => true],
                        ['name' => 'authority_scope', 'type' => 'textarea', 'required' => true],
                    ],
                ],
                'template_layout' => '<h1>Surat Kuasa</h1><p>{{authority_scope}}</p>',
            ],
        ];

        foreach ($templates as $template) {
            $categoryId = $categoryMap[$template['category']] ?? null;
            if (!$categoryId) {
                continue;
            }

            DocumentTemplate::query()->updateOrCreate(
                [
                    'category_id' => $categoryId,
                    'name' => $template['name'],
                ],
                [
                    'type' => $template['type'],
                    'form_schema' => $template['form_schema'],
                    'template_layout' => $template['template_layout'],
                ]
            );
        }
    }
}
