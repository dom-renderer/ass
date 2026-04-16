<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $assets;

    public function __construct($assets)
    {
        $this->assets = $assets;
    }

    public function collection()
    {
        return $this->assets;
    }

    public function headings(): array
    {
        return [
            'ASSET NAME',
            'CODE',
            'UNIQUE CODE',
            'STATUS',
            'LOCATION',
            'DOM',
            'PO DATE',
            'LIFESPAN (MONTHS)',
            'EXPIRY DATE',
            'DAYS TO EXPIRE',
            'WARRANTY (MONTHS)',
            'WARRANTY EXPIRY DATE',
            'WARRANTY DAYS TO EXPIRE',
            'QR LABEL PDF',
            'BARCODE LABEL PDF',
        ];
    }

    public function map($asset): array
    {
        $poDate = !empty($asset->po_date) ? Carbon::parse($asset->po_date) : null;
        $expiryDate = $poDate ? $poDate->copy()->addMonths((int) ($asset->lifespan ?? 0)) : null;
        $daysToExpire = $expiryDate ? Carbon::now()->diffInDays($expiryDate, false) : null;

        $warrantyExpiry = null;
        $warrantyDays = null;
        try {
            $warrantyExpiry = $asset->warranty_expiry_date ?? null;
            $warrantyDays = $asset->warranty_days_to_expire ?? null;
        } catch (\Throwable $e) {
            $warrantyExpiry = null;
            $warrantyDays = null;
        }

        $dom = '';
        if (!empty($asset->dom)) {
            $dom = trim(($asset->dom->employee_id ?? '') . ' - ' . ($asset->dom->name ?? ''));
        }

        return [
            $asset->name ?? '',
            $asset->code ?? '',
            $asset->ucode ?? '',
            $asset->assetStatus->title ?? '',
            $asset->store->name ?? '',
            $dom,
            $poDate ? $poDate->format('Y-m-d') : '',
            (int) ($asset->lifespan ?? 0),
            $expiryDate ? $expiryDate->format('Y-m-d') : '',
            $daysToExpire === null ? '' : $daysToExpire,
            (int) ($asset->warranty ?? 0),
            is_string($warrantyExpiry) ? $warrantyExpiry : (is_object($warrantyExpiry) && method_exists($warrantyExpiry, 'format') ? $warrantyExpiry->format('Y-m-d') : ''),
            $warrantyDays === null ? '' : $warrantyDays,
            $asset->scan_links['qr_code_label'] ?? '',
            $asset->scan_links['barcode_label'] ?? '',
        ];
    }
}

