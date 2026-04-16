@extends('layouts.app-master')

@section('content')
<div class="bg-light p-4 rounded">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h3 mb-0">{{ $page_title }}</h1>
            <p class="text-muted mb-0">Store: {{ $location->name }} ({{ $location->code }})</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('store_qr_codes.download')
                @if($qrCodes->isNotEmpty())
                    <a href="{{ route('locations.qr-codes.download-all', $location->id) }}" class="btn btn-primary btn-sm">Download all (ZIP)</a>
                @endif
            @endcan
            <a href="{{ route('stores.edit', $location->id) }}" class="btn btn-outline-secondary btn-sm">Back to location</a>
        </div>
    </div>
    @include('layouts.partials.messages')
    @if($qrCodes->isEmpty())
        <p class="alert alert-info">No table QR codes yet. Set <strong>Number of tables</strong> on the location and save to generate codes.</p>
    @else
        <div class="row g-3">
            @foreach($qrCodes as $qr)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-2 flex-grow-1 d-flex align-items-center justify-content-center">
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->margin(1)->generate($qr->qr_label) !!}
                            </div>
                            <div class="fw-semibold small mb-2">{{ $qr->qr_label }}</div>
                            @can('store_qr_codes.download')
                                <a href="{{ route('locations.qr-codes.download', [$location->id, $qr->id]) }}" class="btn btn-sm btn-outline-primary mt-auto">Download PNG</a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
