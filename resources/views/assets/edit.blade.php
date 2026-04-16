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
            grid-template-columns: 1.4fr 2fr auto;
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

        .doc-title-row.new-doc {
            grid-template-columns: 1.4fr 2fr;
        }

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
            margin-right: 27px !important;
            font-size: 25px !important;
        }

        /* Keep linked-assets table at 45/45/10 widths */
        #assets-table {
            width: 100% !important;
            table-layout: fixed;
        }

        #assets-table col.col-asset,
        #assets-table th.col-asset,
        #assets-table td.col-asset {
            width: 45% !important;
        }

        #assets-table col.col-description,
        #assets-table th.col-description,
        #assets-table td.col-description {
            width: 45% !important;
        }

        #assets-table col.col-action,
        #assets-table th.col-action,
        #assets-table td.col-action {
            width: 10% !important;
        }

        #assets-table td .select2-container,
        #assets-table td .form-control {
            width: 100% !important;
            max-width: 100%;
        }
    </style>
@endpush

@section('content')
    <div data-afs>
        <form method="POST" action="{{ route('assets.update', $store->id) }}" class="gift-submit-form"
            enctype="multipart/form-data"> @csrf
            <div class="afs-shell">
                <div class="afs-head">
                    <h2>Edit Asset</h2>
                </div>
                <div class="afs-body">
                    <div class="row">
                        @method('PATCH')
                        <div class="col-12">
                            <div class="fursa-form row">
                                <div class="col-6">
                                    <div class="afs-card">
                                        <div class="afs-card-h">
                                            <span class="afs-icon blue"><i class="bi bi-diagram-3"></i></span>
                                            <p class="afs-title">Classification & Assignment</p>
                                        </div>
                                        <div class="afs-card-b">

                                            <input name="location" id="location" type="hidden"
                                                value="{{ $store->location }}">

                                            <div class="form-group">
                                                <div class="form-row">
                                                    <div class="col-12">
                                                        <label class="form-label" for="model_type"> Make <span
                                                                class="text-danger">*</span> </label>
                                                        <select name="model_type" id="model_type" class="form-control"
                                                            required>
                                                            <option value=""></option>
                                                            @foreach ($modelTypes as $typeRow)
                                                                <option value="{{ $typeRow->id }}"
                                                                    @if ($typeRow->id == $store->model_type) selected @endif>
                                                                    {{ $typeRow->name }} </option>
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
                                                                <option value="{{ $typeRow->id }}"
                                                                    @if ($typeRow->id == $store->store_type) selected @endif>
                                                                    {{ $typeRow->name }} </option>
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
                                                            class="form-control" required>
                                                            <option value=""></option>
                                                            @foreach ($storeCategories as $category_row)
                                                                <option value="{{ $category_row->id }}"
                                                                    @if ($category_row->id == $store->store_category) selected @endif>
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
                                                            @foreach ($locationItems as $locationItem)
                                                                <option value="{{ $locationItem->id }}" selected>
                                                                    {{ $locationItem->code }} - {{ $locationItem->name }}
                                                                </option>
                                                            @endforeach
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
                                                        <select name="dom_id" id="dom_id">
                                                            @foreach ($assignTo as $assign)
                                                                <option value="{{ $assign->id }}" selected>
                                                                    {{ $assign->employee_id }} - {{ $assign->name }}
                                                                    {{ $assign->middle_name }} {{ $assign->last_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
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
                                                        <label class="form-label" for="locname"> Name </label>
                                                        <input name="name" type="text" class="form-control"
                                                            value="{{ old('name', $store->name) }}"
                                                            placeholder="Location Name" required>
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
                                                            id="ucode" placeholder="Unique Code"
                                                            value="{{ old('ucode', $store->ucode) }}" required>
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
                                                            value="{{ old('code', $store->code) }}"
                                                            placeholder="Location Code">
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
                                                            id="po_date" placeholder="PO Date"
                                                            value="{{ date('Y-m-d', strtotime(old('po_date', $store->po_date))) }}"
                                                            required>
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
                                                            id="warranty" placeholder="Warranty (Months)"
                                                            value="{{ old('warranty', $store->warranty) }}" required>
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
                                                            id="warranty" placeholder="Lifespan (Months)"
                                                            value="{{ old('lifespan', $store->lifespan) }}" required>
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
                                                                    @if ($status->id == $store->asset_status_id) selected @endif
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
                                                        @if ($store->primary_image)
                                                            <div class="mt-2">
                                                                <p>Current Image:</p>
                                                                <img src="{{ asset('storage/assets-images/' . $store->primary_image) }}"
                                                                    alt="Primary Image"
                                                                    style="max-width: 200px; max-height: 200px;">
                                                            </div>
                                                        @endif
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

                                                        @if ($store->secondary_images)
                                                            <div class="mt-2">
                                                                <p>Current Secondary Images:</p>
                                                                <div class="row">
                                                                    @foreach ($store->secondary_images as $image)
                                                                        <div class="col-md-3 mb-2 text-center">
                                                                            <img src="{{ asset('storage/assets-images/' . $image) }}"
                                                                                alt="Secondary Image"
                                                                                class="img-fluid img-thumbnail"
                                                                                style="height: 100px; object-fit: cover;">
                                                                            <div class="form-check mt-1">
                                                                                <input class="form-check-input"
                                                                                    type="checkbox"
                                                                                    name="remove_secondary_images[]"
                                                                                    value="{{ $image }}"
                                                                                    id="remove_{{ $loop->index }}">
                                                                                <label class="form-check-label text-danger"
                                                                                    for="remove_{{ $loop->index }}">
                                                                                    Remove
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
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
                                                            <span
                                                                class="text-danger text-left d-block mt-1">{{ $message }}</span>
                                                        @enderror

                                                        @if ($store->documents)
                                                            <div class="mt-3">
                                                                <p><strong>Current Documents:</strong></p>
                                                                <div>
                                                                    @foreach ($store->documents as $index => $document)
                                                                        <div class="doc-title-row">
                                                                            <a class="doc-file-name"
                                                                                href="{{ asset('storage/asset-documents/' . $document) }}"
                                                                                target="_blank">
                                                                                <i class="bi bi-file-earmark"></i>
                                                                                {{ $document }}
                                                                            </a>
                                                                            <input type="text"
                                                                                name="existing_document_titles[{{ $document }}]"
                                                                                class="form-control"
                                                                                value="{{ old('existing_document_titles', [])[$document] ?? $store->getDocumentTitleByFile($document) }}"
                                                                                placeholder="Document title (required)">
                                                                            <div class="form-check m-0">
                                                                                <input class="form-check-input"
                                                                                    type="checkbox"
                                                                                    name="remove_documents[]"
                                                                                    value="{{ $document }}"
                                                                                    id="remove_doc_{{ $index }}">
                                                                                <label class="form-check-label text-danger"
                                                                                    for="remove_doc_{{ $index }}">Remove</label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
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
                                                    <colgroup>
                                                        <col class="col-asset">
                                                        <col class="col-description">
                                                        <col class="col-action">
                                                    </colgroup>
                                                    <thead>
                                                        <tr>
                                                            <th class="col-asset">Asset</th>
                                                            <th class="col-description">Description</th>
                                                            <th class="col-action">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($locationAssets as $index => $locationAsset)
                                                            @if (isset($locationAsset->asset->id))
                                                                <tr id="asset-row-{{ $index }}">
                                                                    <td class="col-asset">
                                                                        <select
                                                                            name="assets[{{ $index }}][asset_id]"
                                                                            class="select2 asset-select" required>
                                                                            <option value="{{ $locationAsset->asset_id }}"
                                                                                selected>{{ $locationAsset->asset->code }}
                                                                                - {{ $locationAsset->asset->name }}
                                                                            </option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="col-description">
                                                                        <input type="text"
                                                                            name="assets[{{ $index }}][description]"
                                                                            class="form-control"
                                                                            placeholder="Description (Optional)"
                                                                            value="{{ $locationAsset->description }}">
                                                                    </td>
                                                                    <td class="col-action">
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm remove-asset-btn"
                                                                            data-row-id="{{ $index }}"><i
                                                                                class="bi bi-trash"></i> </button>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn btn-success btn-sm mt-2"
                                                    id="add-asset-btn"><i class="bi bi-plus"></i> Add Asset</button>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $qrCodeImageUrl = null;
                                        $barcodeImageUrl = null;
                                        $qrCodeImagePath = public_path('storage/qr-codes/' . $store->ucode . '.png');
                                        $barcodeImagePath = public_path('storage/barcodes/' . $store->ucode . '.png');
                                        if (!empty($store->ucode) && file_exists($qrCodeImagePath)) {
                                            $qrCodeImageUrl = asset('storage/qr-codes/' . $store->ucode . '.png');
                                        }
                                        if (!empty($store->ucode) && file_exists($barcodeImagePath)) {
                                            $barcodeImageUrl = asset('storage/barcodes/' . $store->ucode . '.png');
                                        }
                                    @endphp

                                    @if ($qrCodeImageUrl || $barcodeImageUrl)
                                        <div class="afs-card">
                                            <div class="afs-card-h">
                                                <span class="afs-icon purple"><i class="bi bi-upc-scan"></i></span>
                                                <p class="afs-title">Asset Labels</p>
                                            </div>
                                            <div class="afs-card-b">
                                                <div class="row">
                                                    @if ($qrCodeImageUrl)
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label d-block mb-2">QR Code</label>
                                                            <div class="border rounded p-2 text-center bg-white">
                                                                <img src="{{ $qrCodeImageUrl }}" alt="QR Code"
                                                                    class="img-fluid mb-2" style="max-height: 180px;">
                                                                <div>
                                                                    <a href="{{ $qrCodeImageUrl }}" download="{{ $store->ucode }}-qr.png"
                                                                        class="ofs-btn ofs-btn-outline">
                                                                        <i class="bi bi-download"></i> Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($barcodeImageUrl)
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label d-block mb-2">Barcode</label>
                                                            <div class="border rounded p-2 text-center bg-white">
                                                                <img src="{{ $barcodeImageUrl }}" alt="Barcode"
                                                                    class="img-fluid mb-2" style="max-height: 180px;">
                                                                <div>
                                                                    <a href="{{ $barcodeImageUrl }}" download="{{ $store->ucode }}-barcode.png"
                                                                        class="ofs-btn ofs-btn-outline">
                                                                        <i class="bi bi-download"></i> Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
                                    Update Asset
                                </button>
                                <a href="{{ route('assets.index') }}" class="ofs-btn ofs-btn-ghost">
                                    ← Back to Assets
                                </a>
                            </div>
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
                    const fileName = fileItem.file && fileItem.file.name ? fileItem.file.name : (
                        'Document ' + (index + 1));
                    documentTitleWrap.append(`
                    <div class="doc-title-row new-doc">
                        <div class="doc-file-name" title="${fileName}">${fileName}</div>
                        <div><input type="text" name="document_titles[]" class="form-control" placeholder="Document title (required)" required></div>
                    </div>
                `);
                });
            }
            pond3.on('addfile', renderDocumentTitleInputs);
            pond3.on('removefile', renderDocumentTitleInputs);

            $('.gift-submit-form').on('submit', function(e) {
                const seen = [];
                let hasError = false;

                $('input[name="document_titles[]"], input[name^="existing_document_titles["]').removeClass(
                    'is-invalid');

                $('input[name^="existing_document_titles["]').each(function() {
                    const container = $(this).closest('.doc-title-row');
                    const removeCheck = container.find('input[name="remove_documents[]"]');
                    if (removeCheck.length && removeCheck.is(':checked')) {
                        return;
                    }
                    const val = String($(this).val() || '').trim().toLowerCase();
                    if (!val || seen.includes(val)) {
                        $(this).addClass('is-invalid');
                        hasError = true;
                    } else {
                        seen.push(val);
                    }
                });

                $('input[name="document_titles[]"]').each(function() {
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
                    Swal.fire('Invalid document titles',
                        'Each kept/uploaded document must have a unique title.', 'error');
                }
            });

            $('#asset_status_id').select2({
                placeholder: 'Select Status',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('.select2').select2({
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
                placeholder: 'Select Make',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#model_type').select2({
                placeholder: 'Select Type',
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

            let assetRowIndex = {{ count($locationAssets) }};

            $('#add-asset-btn').on('click', function() {
                let html = `
                <tr id="asset-row-${assetRowIndex}">
                    <td class="col-asset">
                        <select name="assets[${assetRowIndex}][asset_id]" class="form-control asset-select" required></select>
                    </td>
                    <td class="col-description">
                        <input type="text" name="assets[${assetRowIndex}][description]" class="form-control" placeholder="Description (Optional)">
                    </td>
                    <td class="col-action">
                        <button type="button" class="btn btn-danger btn-sm remove-asset-btn" data-row-id="${assetRowIndex}"><i class="bi bi-trash"></i> </button>
                    </td>
                </tr>
            `;
                $('#assets-table tbody').append(html);

                initializeAssetSelect2(assetRowIndex);
                assetRowIndex++;
            });

            $(document).on('click', '.remove-asset-btn', function() {
                let that = this;

                Swal.fire({
                    title: 'Are you sure you want remove this sub-asset from the asset?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let rowId = $(this).data('row-id');
                        $('#asset-row-' + rowId).remove();
                        return true;
                    } else {
                        return false;
                    }
                })
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
                                assets: 1,
                                exceptThis: "{{ $store->id }}"
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
