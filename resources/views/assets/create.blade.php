@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

    <style type="text/css">
        #map {
            height: 650px;
            width: 100%;
        }

        #description {
            font-family: Roboto;
            font-size: 15px;
            font-weight: 300;
        }

        #infowindow-content .title {
            font-weight: bold;
        }

        #infowindow-content {
            display: none;
        }

        #map #infowindow-content {
            display: inline;
        }

        .pac-card {
            background-color: #fff;
            border: 0;
            border-radius: 2px;
            box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
            margin: 10px;
            padding: 0 0.5em;
            font: 400 18px Roboto, Arial, sans-serif;
            overflow: hidden;
            font-family: Roboto;
            padding: 0;
        }

        #pac-container {
            padding-bottom: 12px;
            margin-right: 12px;
            z-index: 99999;
        }

        .pac-controls {
            display: inline-block;
            padding: 5px 11px;
        }

        .pac-controls label {
            font-family: Roboto;
            font-size: 13px;
            font-weight: 300;
        }

        #pac-input {
            background-color: #fff;
            font-family: Roboto;
            font-size: 15px;
            font-weight: 300;
            margin-left: 12px;
            padding: 0 11px 0 13px;
            text-overflow: ellipsis;
            width: 400px;
            position: absolute;
            top: 11px;
            height: 40px;
            left: 188px;
        }

        #pac-input:focus {
            border-color: #4d90fe;
        }

        #title {
            color: #fff;
            background-color: #4d90fe;
            font-size: 25px;
            font-weight: 500;
            padding: 6px 12px;
        }

        #target {
            width: 345px;
        }

        div[id^=map_canvas],
        div[id^=map_canvas] div {
            overflow: auto;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .pac-container {
            background-color: #FFF;
            z-index: 2000;
            position: fixed;
            display: inline-block;
        }

        .select2-container .select2-search--inline .select2-search__field {
            height: 20px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 37px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }

        .select2-container {
            background: none;
            border: none;
        }

        [data-afs] .afs-shell {
            background: #fff;
            border: 1px solid #e8eaf0;
            border-radius: 14px;
            overflow: hidden;
        }

        [data-afs] .afs-head {
            padding: 14px 18px;
            border-bottom: 1px solid #eef1f6;
            background: #fafbfd;
        }

        [data-afs] .afs-head h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #161922;
        }

        [data-afs] .afs-body {
            padding: 16px;
        }

        [data-afs] .afs-card {
            background: #fff;
            border: 1px solid #e8eaf0;
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        [data-afs] .afs-card-h {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-bottom: 1px dashed #e6e9f0;
            background: #fbfcfe;
        }

        [data-afs] .afs-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        [data-afs] .afs-icon.blue {
            background: #eef4ff;
            color: #3b6ecc;
        }

        [data-afs] .afs-icon.green {
            background: #edfaf3;
            color: #1d8a55;
        }

        [data-afs] .afs-icon.amber {
            background: #fff8ec;
            color: #b06f10;
        }

        [data-afs] .afs-icon.purple {
            background: #f3f1ff;
            color: #6447d6;
        }

        [data-afs] .afs-title {
            margin: 0;
            font-weight: 600;
            font-size: .88rem;
            color: #1a1c22;
        }

        [data-afs] .afs-card-b {
            padding: 12px;
        }

        [data-afs] .form-group {
            margin-bottom: 12px;
        }

        [data-afs] .form-label {
            font-size: .76rem;
            font-weight: 500;
            color: #5a5f6e;
            margin-bottom: 5px;
        }

        [data-afs] .form-control,
        [data-afs] .select2-container--classic .select2-selection--single {
            height: 40px !important;
            border: 1px solid #dde0ea !important;
            border-radius: 8px !important;
            background: #fafbfd !important;
            font-size: .84rem !important;
            box-shadow: none !important;
        }

        [data-afs] .form-control:focus {
            border-color: #3b6ecc !important;
            box-shadow: 0 0 0 3px rgba(59, 110, 204, .12) !important;
            background: #fff !important;
        }

        [data-afs] .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            color: #1a1c22 !important;
        }

        [data-afs] .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .doc-title-row {
            display: grid;
            grid-template-columns: 1.4fr 2fr;
            gap: 10px;
            align-items: center;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 8px;
            background: #fff;
        }
        .doc-title-row .doc-file-name {
            font-size: 12px;
            color: #475569;
            word-break: break-all;
        }

        [data-ofs] * {
            box-sizing: border-box;
        }

        [data-ofs] {
            background: #F5F6F8;
            min-height: 100vh;
            padding: 2rem 1.5rem;
        }

        /* ── Page header ── */
        .ofs-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .ofs-header h1 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #0E0F11;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .ofs-breadcrumb {
            font-size: 0.78rem;
            color: #8A8F9C;
            margin-top: 2px;
        }

        /* ── Section card ── */
        .ofs-card {
            background: #ffffff;
            border: 1px solid #E8EAF0;
            border-radius: 14px;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .ofs-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid #F0F2F7;
            background: #FAFBFD;
        }

        .ofs-card-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ofs-card-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .ofs-card-icon.blue {
            background: #EEF4FF;
            color: #3B6ECC;
        }

        .ofs-card-icon.green {
            background: #EDFAF3;
            color: #1D8A55;
        }

        .ofs-card-icon.amber {
            background: #FFF8EC;
            color: #B06F10;
        }

        .ofs-card-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: #1A1C22;
            margin: 0;
        }

        .ofs-card-subtitle {
            font-size: 0.75rem;
            color: #8A8F9C;
            margin: 0;
        }

        .ofs-card-body {
            padding: 1.4rem 1.5rem;
        }

        /* ── Field grid ── */
        .ofs-field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        /* ── Field label + control ── */
        .ofs-field {}

        .ofs-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 500;
            color: #5A5F6E;
            margin-bottom: 5px;
            letter-spacing: 0.01em;
        }

        .ofs-label .req {
            color: #D94E4E;
            margin-left: 1px;
        }

        /* Override Bootstrap form-control within our scope */
        [data-ofs] .form-control,
        [data-ofs] .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #DDE0EA !important;
            border-radius: 8px !important;
            background: #FAFBFD !important;
            font-size: 0.84rem !important;
            color: #1A1C22 !important;
            padding: 0 12px !important;
            box-shadow: none !important;
            transition: border-color 0.15s, box-shadow 0.15s;
            line-height: 38px !important;
        }

        [data-ofs] .form-control:focus {
            border-color: #3B6ECC !important;
            box-shadow: 0 0 0 3px rgba(59, 110, 204, 0.12) !important;
            background: #fff !important;
        }

        [data-ofs] .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3B6ECC !important;
            box-shadow: 0 0 0 3px rgba(59, 110, 204, 0.12) !important;
        }

        [data-ofs] .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 0 !important;
            color: #1A1C22 !important;
            font-size: 0.84rem !important;
        }

        [data-ofs] .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            top: 0 !important;
            right: 8px !important;
        }

        [data-ofs] .select2-dropdown {
            border: 1px solid #DDE0EA !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden;
        }

        /* ── Item line card ── */
        .ofs-item-card {
            background: #FAFBFD;
            border: 1px solid #E8EAF0;
            border-radius: 10px;
            padding: 1rem 1.1rem;
            position: relative;
            transition: border-color 0.15s;
        }

        .ofs-item-card:hover {
            border-color: #C5CBE0;
        }

        .ofs-item-grid {
            display: grid;
            grid-template-columns: 2fr 3fr 80px 120px 38px;
            gap: 10px;
            align-items: end;
        }

        @media (max-width: 768px) {
            .ofs-item-grid {
                grid-template-columns: 1fr 1fr;
            }

            .ofs-item-grid>*:last-child {
                grid-column: span 2;
            }
        }

        /* ── Customization accordion ── */
        .ofs-custom-toggle {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 6px 10px;
            font-size: 0.76rem;
            font-weight: 500;
            color: #3B6ECC;
            background: #EEF4FF;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: fit-content;
        }

        .ofs-custom-toggle:hover {
            background: #DDE8FA;
        }

        .ofs-custom-panel {
            margin-top: 10px;
            padding: 12px;
            background: #fff;
            border: 1px solid #E8EAF0;
            border-radius: 8px;
        }

        .ofs-summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            color: #5A5F6E;
            background: #F0F2F7;
            border-radius: 4px;
            padding: 2px 7px;
            margin-top: 8px;
        }

        /* ── Summary panel ── */
        .ofs-summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.84rem;
            color: #5A5F6E;
            border-bottom: 1px dashed #EEF0F7;
        }

        .ofs-summary-row:last-child {
            border-bottom: none;
        }

        .ofs-summary-row.total {
            font-size: 1rem;
            font-weight: 600;
            color: #0E0F11;
            border-top: 1.5px solid #E8EAF0;
            margin-top: 4px;
            padding-top: 12px;
        }

        .ofs-summary-value {
            font-family: 'DM Mono', monospace;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .ofs-summary-row.total .ofs-summary-value {
            font-size: 1.05rem;
            color: #1D8A55;
        }

        .ofs-discount-value {
            color: #D94E4E;
        }

        /* ── Buttons ── */
        .ofs-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.84rem;
            font-weight: 500;
            border-radius: 9px;
            padding: 9px 18px;
            border: none;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.1s;
            text-decoration: none;
        }

        .ofs-btn:active {
            transform: scale(0.98);
        }

        .ofs-btn-primary {
            background: #1A1C22;
            color: #ffffff;
        }

        .ofs-btn-primary:hover {
            background: #2D3142;
            color: #fff;
        }

        .ofs-btn-outline {
            background: transparent;
            border: 1px solid #DDE0EA;
            color: #3B6ECC;
        }

        .ofs-btn-outline:hover {
            background: #EEF4FF;
        }

        .ofs-btn-ghost {
            background: transparent;
            border: 1px solid #DDE0EA;
            color: #8A8F9C;
        }

        .ofs-btn-ghost:hover {
            background: #F5F6F8;
            color: #1A1C22;
        }

        .ofs-btn-danger-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #FFF0F0;
            border: 1px solid #F5C2C2;
            color: #D94E4E;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            flex-shrink: 0;
            transition: background 0.15s;
        }

        .ofs-btn-danger-icon:hover {
            background: #FFE0E0;
        }

        /* ── Add item ── */
        .ofs-add-item-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 11px;
            border: 1.5px dashed #C5CBE0;
            border-radius: 10px;
            background: transparent;
            color: #8A8F9C;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            margin-top: 10px;
        }

        .ofs-add-item-btn:hover {
            border-color: #3B6ECC;
            color: #3B6ECC;
            background: #EEF4FF;
        }

        /* ── Coupon hint ── */
        .ofs-coupon-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #1D8A55;
            background: #EDFAF3;
            border: 1px solid #B4E8CF;
            border-radius: 6px;
            padding: 4px 10px;
            margin-top: 8px;
        }

        /* form-check inside our card */
        [data-ofs] .form-check {
            margin-bottom: 4px;
        }

        [data-ofs] .form-check-label {
            font-size: 0.80rem;
            color: #3A3D48;
        }

        [data-ofs] .form-check-input {
            margin-top: 2px;
        }

        /* ── footer action bar ── */
        .ofs-action-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1.1rem 1.5rem;
            background: #fff;
            border: 1px solid #E8EAF0;
            border-radius: 14px;
            margin-top: 0.5rem;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            margin-right:27px!important;
            font-size:25px!important;
        }


    </style>
@endpush

@section('content')
    <div data-afs>
        <form method="POST" action="{{ route('assets.store') }}" class="gift-submit-form" enctype="multipart/form-data">
            @csrf
            <div class="afs-shell">
                <div class="afs-head">
                    <h2>Add Asset</h2>
                </div>
                <div class="afs-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="afs-card">
                                <div class="afs-card-h">
                                    <span class="afs-icon blue"><i class="bi bi-diagram-3"></i></span>
                                    <p class="afs-title">Classification & Assignment</p>
                                </div>
                                <div class="afs-card-b">

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="model_type"> Make <span
                                                        class="text-danger">*</span> </label>
                                                <select name="model_type" id="model_type" class="form-control"
                                                    required>
                                                    <option value=""></option>
                                                    @foreach ($modelTypes as $typeRow)
                                                        <option value="{{ $typeRow->id }}"> {{ $typeRow->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('model_type'))
                                        <span
                                            class="text-danger text-left">{{ $errors->first('model_type') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="store_type"> Model <span
                                                        class="text-danger">*</span> </label>
                                                <select name="store_type" id="store_type" class="form-control"
                                                    required>
                                                    <option value=""></option>
                                                    @foreach ($storeTypes as $typeRow)
                                                        <option value="{{ $typeRow->id }}"> {{ $typeRow->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('store_type'))
                                        <span
                                            class="text-danger text-left">{{ $errors->first('store_type') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="store_category">Category <span
                                                        class="text-danger">*</span> </label>
                                                <select name="store_category" id="store_category"
                                                    class="form-control">
                                                    <option value=""></option>
                                                    @foreach ($storeCategories as $category_row)
                                                        <option value="{{ $category_row->id }}">
                                                            {{ $category_row->name }} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('store_category'))
                                        <span
                                            class="text-danger text-left">{{ $errors->first('store_category') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="location_item">Location </label>
                                                <select name="location_item" id="location_item"
                                                    class="form-control">
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('location_item'))
                                        <span
                                            class="text-danger text-left">{{ $errors->first('location_item') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="dom_id"> Assign To </label>
                                                <select name="dom_id" id="dom_id"></select>
                                                @if ($errors->has('dom_id'))
                                                    <span
                                                        class="text-danger text-left">{{ $errors->first('dom_id') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('dom_id'))
                                        <span class="text-danger text-left">{{ $errors->first('dom_id') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="afs-card">
                                <div class="afs-card-h">
                                    <span class="afs-icon purple"><i class="bi bi-upc-scan"></i></span>
                                    <p class="afs-title">Identity & Codes</p>
                                </div>
                                <div class="afs-card-b">

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="locname"> Name <span
                                                        class="text-danger">*</span> </label>
                                                <input name="name" type="text" class="form-control"
                                                    id="locname" placeholder="Asset Name"
                                                    value="{{ old('name') }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('name'))
                                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="loccode"> Unique Code <span
                                                        class="text-danger">*</span> </label>
                                                <input name="ucode" type="text" class="form-control"
                                                    id="ucode" placeholder="Unique Code" required>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('ucode'))
                                        <span class="text-danger text-left">{{ $errors->first('ucode') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="loccode"> Internal Code </label>
                                                <input name="code" type="text" class="form-control"
                                                    id="loccode" placeholder="Internal Code">
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('code'))
                                        <span class="text-danger text-left">{{ $errors->first('code') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="afs-card">
                                <div class="afs-card-h">
                                    <span class="afs-icon amber"><i class="bi bi-calendar-check"></i></span>
                                    <p class="afs-title">Purchase & Lifecycle</p>
                                </div>
                                <div class="afs-card-b">

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="po_date"> PO Date <span
                                                        class="text-danger">*</span> </label>
                                                <input name="po_date" type="date" class="form-control"
                                                    id="po_date" placeholder="PO Date" required>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('po_date'))
                                        <span class="text-danger text-left">{{ $errors->first('po_date') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="warranty"> Warranty (Months) <span
                                                        class="text-danger">*</span> </label>
                                                <input name="warranty" type="number" min="0"
                                                    step="1" max="1000" class="form-control"
                                                    id="warranty" placeholder="Warranty (Months)" required>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('warranty'))
                                        <span
                                            class="text-danger text-left">{{ $errors->first('warranty') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="warranty"> Lifespan (Months) <span
                                                        class="text-danger">*</span> </label>
                                                <input name="lifespan" type="number" min="0"
                                                    step="1" max="1000" class="form-control"
                                                    id="warranty" placeholder="Lifespan (Months)" required>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('lifespan'))
                                        <span
                                            class="text-danger text-left">{{ $errors->first('lifespan') }}</span>
                                    @endif

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="asset_status_id"> Asset Status
                                                    <span class="text-danger">*</span> </label>
                                                <select name="asset_status_id" class="form-control" required>
                                                    @foreach ($assetStatuses as $status)
                                                        <option value="{{ $status->id }}"
                                                            style="color: {{ $status->color }}; font-weight: bold;">
                                                            {{ $status->title }} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($errors->has('asset_status_id'))
                                        <span
                                            class="text-danger text-left">{{ $errors->first('asset_status_id') }}</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="col-6">
                            <div class="afs-card">
                                <div class="afs-card-h">
                                    <span class="afs-icon green"><i class="bi bi-images"></i></span>
                                    <p class="afs-title">Media & Documents</p>
                                </div>
                                <div class="afs-card-b">

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="primary_image"> Primary Image
                                                </label>
                                                <input type="file" class="filepond" name="primary_image"
                                                    id="primary_image" data-max-file-size="3MB">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="secondary_images"> Secondary Images
                                                </label>
                                                <input type="file" class="filepond" name="secondary_images[]"
                                                    id="secondary_images" multiple data-max-file-size="3MB">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-12">
                                                <label class="form-label" for="documents"> Documents </label>
                                                <input type="file" class="filepond" name="documents[]"
                                                    id="documents" multiple data-max-file-size="10MB">
                                                <small class="text-muted">Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG,
                                                    PNG (Max 10MB each)</small>
                                                <div id="document-title-wrap" class="mt-2"></div>
                                                @error('document_titles')
                                                    <span class="text-danger text-left d-block mt-1">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="afs-card">
                                <div class="afs-card-h">
                                    <span class="afs-icon blue"><i class="bi bi-link-45deg"></i></span>
                                    <p class="afs-title">Linked Assets</p>
                                </div>
                                <div class="afs-card-b">

                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="assets-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 45%">Asset</th>
                                                    <th style="width: 45%">Description</th>
                                                    <th style="width: 10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <button type="button" class="ofs-btn btn-primary"
                                            id="add-asset-btn"><i class="bi bi-plus"></i> Add Asset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ofs-action-bar">
                            <button type="submit" class="ofs-btn btn-primary">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M13 2H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V5l-3-3z"></path>
                                    <polyline points="13 5 10 5 10 2"></polyline>
                                    <line x1="7" y1="9" x2="9" y2="9"></line>
                                    <line x1="8" y1="8" x2="8" y2="10"></line>
                                </svg>
                                Save Asset
                            </button>
                            <a href="{{ route('assets.index') }}" class="ofs-btn ofs-btn-ghost">
                                ← Back to Assets
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

    <script>
        $(document).ready(function() {
            FilePond.registerPlugin(FilePondPluginImagePreview);

            const inputElement = document.querySelector('input[id="primary_image"]');
            const pond = FilePond.create(inputElement, {
                storeAsFile: true,
            });

            const inputElement2 = document.querySelector('input[id="secondary_images"]');
            const pond2 = FilePond.create(inputElement2, {
                storeAsFile: true,
            });

            const inputElement3 = document.querySelector('input[id="documents"]');
            const pond3 = FilePond.create(inputElement3, {
                storeAsFile: true,
            });
            const documentTitleWrap = $('#document-title-wrap');
            function renderDocumentTitleInputs() {
                const files = pond3.getFiles();
                documentTitleWrap.empty();
                files.forEach(function(fileItem, index) {
                    const fileName = fileItem.file && fileItem.file.name ? fileItem.file.name : ('Document ' + (index + 1));
                    documentTitleWrap.append(`
                        <div class="doc-title-row">
                            <div class="doc-file-name" title="${fileName}">${fileName}</div>
                            <div><input type="text" name="document_titles[]" class="form-control" placeholder="Document title (required)" required></div>
                        </div>
                    `);
                });
            }
            pond3.on('addfile', renderDocumentTitleInputs);
            pond3.on('removefile', renderDocumentTitleInputs);

            $('.gift-submit-form').on('submit', function(e) {
                const titleInputs = $('input[name="document_titles[]"]');
                const seen = [];
                let hasError = false;

                titleInputs.removeClass('is-invalid');
                titleInputs.each(function() {
                    const val = String($(this).val() || '').trim().toLowerCase();
                    if (!val || seen.includes(val)) {
                        $(this).addClass('is-invalid');
                        hasError = true;
                    } else {
                        seen.push(val);
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    Swal.fire('Invalid document titles', 'Each uploaded document must have a unique title.', 'error');
                }
            });

            $('#asset_status_id').select2({
                placeholder: 'Select Status',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#location_item').select2({
                placeholder: 'Select Location',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('assets-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {});

            $('#filter_dom').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            roles: "{{ implode(',', [Helper::$roles['divisional-operations-manager']]) }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {

            });

            jQuery.validator.addMethod("extension", function(value, element, param) {
                if (element.files.length > 0) {
                    const file = element.files[0];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    return fileExtension === param.toLowerCase();
                }
                return true;
            }, "Please upload a valid file type.");

            jQuery.validator.addMethod("filesize", function(value, element, param) {
                if (element.files.length > 0) {
                    return element.files[0].size <= param;
                }
                return true;
            }, "File size must not exceed {0} bytes.");

            $('#fileUploader').validate({
                rules: {
                    xlsx: {
                        required: true,
                        extension: 'xlsx'
                    }
                },
                messages: {
                    xlsx: {
                        required: "Please select a file",
                        extension: 'Only .xlsx file is allowed for import'
                    }
                },
                submitHandler: function(form, event) {
                    event.preventDefault();

                    let formData = new FormData(form);

                    $.ajax({
                        url: "{{ route('import-assets') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function() {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function(response) {
                            $('body').find('.LoaderSec').addClass('d-none');
                            if (response.status) {
                                $('#browser-file').modal('hide');
                                $('form#fileUploader')[0].reset();
                                $('.modal-backdrop').remove();

                                Swal.fire('Success', response.message, 'success');
                                location.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });

                }
            });

            function getQueryParams() {
                const params = {};
                const searchParams = new URLSearchParams(window.location.search);
                for (const [key, value] of searchParams.entries()) {
                    params[key] = value;
                }
                return params;
            }

            $('#export-stores').on('click', function() {

                $.ajax({
                    url: "{{ route('export-assets') }}",
                    type: 'GET',
                    cache: false,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    data: getQueryParams(),
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        var url = window.URL || window.webkitURL;
                        var objectUrl = url.createObjectURL(response);
                        var a = $("<a />", {
                            href: objectUrl,
                            download: "stores.xlsx"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });

            // Select2

            $('#state').select2({
                placeholder: 'Select State',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                $('#city').val(null).trigger('change');
            });

            $('#city').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function() {
                                return $('#state option:selected').val();
                            }
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            });

            $('#dom_id').select2({
                placeholder: 'Select User',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            roles: "{{ implode(',', [Helper::$roles['divisional-operations-manager']]) }}"
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            });

            $('#store_type').select2({
                placeholder: 'Select Model',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#model_type').select2({
                placeholder: 'Select Make',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#store_category').select2({
                placeholder: 'Select Category',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            let assetRowIndex = 0;

            $('#add-asset-btn').on('click', function() {
                let html = `
                <tr id="asset-row-${assetRowIndex}">
                    <td>
                        <select name="assets[${assetRowIndex}][asset_id]" class="form-control asset-select" required></select>
                    </td>
                    <td>
                        <input type="text" name="assets[${assetRowIndex}][description]" class="form-control" placeholder="Description (Optional)">
                    </td>
                    <td>
                        <button type="button" class="ofs-btn ofs-btn-danger remove-asset-btn" data-row-id="${assetRowIndex}"><i class="bi bi-trash"></i> </button>
                    </td>
                </tr>
            `;
                $('#assets-table tbody').append(html);

                initializeAssetSelect2(assetRowIndex);
                assetRowIndex++;
            });

            $(document).on('click', '.remove-asset-btn', function() {
                let rowId = $(this).data('row-id');
                $('#asset-row-' + rowId).remove();
            });

            function initializeAssetSelect2(index) {
                $(`select[name="assets[${index}][asset_id]"]`).select2({
                    placeholder: 'Select Asset',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    ajax: {
                        url: "{{ route('assets-list') }}",
                        type: "POST",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                _token: "{{ csrf_token() }}",
                                unassigned_only: 1,
                                assets: 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: $.map(data.items, function(item) {
                                    return {
                                        id: item.id,
                                        text: item.text
                                    };
                                }),
                                pagination: {
                                    more: data.pagination.more
                                }
                            };
                        },
                        cache: true
                    }
                });
            }
        });
    </script>
@endpush
