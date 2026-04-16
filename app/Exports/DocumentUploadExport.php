<?php

namespace App\Exports;

use App\Models\DocumentUpload;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DocumentUploadExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DocumentUpload::with(['document', 'store', 'storeCategory']);

        if (!empty($this->filters['document_type_id'])) {
            $query->where('document_id', $this->filters['document_type_id']);
        }

        if (!empty($this->filters['location_id'])) {
            $query->where('location_id', $this->filters['location_id']);
        }

        if (!empty($this->filters['perpetual']) && $this->filters['perpetual'] != 'all') {
            $perpetualValue = strtolower($this->filters['perpetual']) === 'yes' ? 1 : 0;
            $query->where('perpetual', $perpetualValue);
        }

        if (!empty($this->filters['expiry_from']) && !empty($this->filters['expiry_to'])) {
            $expiryFrom = \Carbon\Carbon::createFromFormat('d-m-Y', $this->filters['expiry_from'])->startOfDay();
            $expiryTo = \Carbon\Carbon::createFromFormat('d-m-Y', $this->filters['expiry_to'])->endOfDay();
            $query->whereBetween('expiry_date', [$expiryFrom, $expiryTo]);
        }

        if (!empty($this->filters['issue_from']) && !empty($this->filters['issue_to'])) {
            $issueFrom = \Carbon\Carbon::createFromFormat('d-m-Y', $this->filters['issue_from'])->startOfDay();
            $issueTo = \Carbon\Carbon::createFromFormat('d-m-Y', $this->filters['issue_to'])->endOfDay();
            $query->whereBetween('issue_date', [$issueFrom, $issueTo]);
        }

        return $query->get();
    }

    public function map($row): array
    {
        return [
            $row->document ? $row->document->name : '-',
            asset('storage/documents') . '/' . $row->file_name,
            $row->proposal_number,
            $row->store ? ($row->store->code . ' - ' . $row->store->name) : '-',
            $row->storeCategory ? $row->storeCategory->name : '-',
            $row->perpetual ? 'Perpetual' : ($row->expiry_date ? \Carbon\Carbon::parse($row->expiry_date)->format('d-m-Y') : '-'),
            $row->issue_date ? \Carbon\Carbon::parse($row->issue_date)->format('d-m-Y') : '-',
            $row->perpetual ? 'Yes' : 'No',
            $row->status,
            $row->remark
        ];
    }

    public function headings(): array
    {
        return [
            'Document Name',
            'File Name',
            'Proposal Number',
            'Location',
            'Category',
            'Expiry Date',
            'Issue Date',
            'Perpetual',
            'Status',
            'Remark'
        ];
    }
}
