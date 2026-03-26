<?php

return [
    'categories' => [
        'perizinan' => 'Perizinan',
        'legal_kontrak' => 'Legal & Kontrak',
        'operasional' => 'Operasional',
        'evaluasi' => 'Evaluasi',
        'lpj' => 'Laporan Pertanggungjawaban',
    ],
    'status_rules' => [
        'pra' => [
            'surat_izin_keramaian',
            'surat_izin_penggunaan_lokasi',
            'mou_vendor_talent',
            'surat_perintah_kerja',
            'surat_kesanggupan_venue',
            'surat_kuasa',
            'site_map_floor_plan',
            'cue_card',
            'daftar_tamu',
            'berita_acara_serah_terima',
        ],
        'running' => [
            'dokumen_evaluasi',
        ],
        'post' => [
            'laporan_pertanggungjawaban',
        ],
        'completed' => [],
    ],
    'templates' => [
        'surat_izin_keramaian' => [
            'title' => 'Surat Izin Keramaian',
            'category' => 'perizinan',
            'required_fields' => ['event_name', 'series_name', 'start_date', 'end_date', 'venue_name'],
        ],
        'surat_izin_penggunaan_lokasi' => [
            'title' => 'Surat Izin Penggunaan Lokasi',
            'category' => 'perizinan',
            'required_fields' => ['event_name', 'series_name', 'venue_name'],
        ],
        'mou_vendor_talent' => [
            'title' => 'MOU Vendor/Talent',
            'category' => 'perizinan',
            'required_fields' => ['event_name', 'series_name', 'vendor_list'],
        ],
        'surat_perintah_kerja' => [
            'title' => 'Surat Perintah Kerja',
            'category' => 'legal_kontrak',
            'required_fields' => ['event_name', 'series_name', 'vendor_list'],
        ],
        'surat_kesanggupan_venue' => [
            'title' => 'Surat Penyertaan Kesanggupan Venue',
            'category' => 'legal_kontrak',
            'required_fields' => ['event_name', 'series_name', 'venue_name'],
        ],
        'surat_kuasa' => [
            'title' => 'Surat Kuasa',
            'category' => 'legal_kontrak',
            'required_fields' => ['event_name', 'series_name'],
        ],
        'site_map_floor_plan' => [
            'title' => 'Site Map / Floor Plan',
            'category' => 'operasional',
            'required_fields' => ['event_name', 'series_name', 'venue_name'],
        ],
        'cue_card' => [
            'title' => 'Cue Card MC & Produksi',
            'category' => 'operasional',
            'required_fields' => ['event_name', 'series_name', 'time_schedule'],
        ],
        'daftar_tamu' => [
            'title' => 'Daftar Tamu',
            'category' => 'operasional',
            'required_fields' => ['event_name', 'series_name', 'attendee_count'],
        ],
        'berita_acara_serah_terima' => [
            'title' => 'Berita Acara Serah Terima',
            'category' => 'operasional',
            'required_fields' => ['event_name', 'series_name', 'vendor_list'],
        ],
        'dokumen_evaluasi' => [
            'title' => 'Dokumen Evaluasi',
            'category' => 'evaluasi',
            'required_fields' => ['event_name', 'series_name', 'start_date', 'end_date'],
        ],
        'laporan_pertanggungjawaban' => [
            'title' => 'Laporan Pertanggungjawaban',
            'category' => 'lpj',
            'required_fields' => ['event_name', 'series_name', 'budget_amount', 'expense_total', 'attendee_count'],
        ],
    ],
];
