@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DynamicAppFont', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

    <style type="text/css">
        body {
            font-family: 'DynamicAppFont', sans-serif !important;
        }

        /* ── Select2 overrides to match Tailwind input height/style ── */
        .select2-container {
            width: 100% !important;
            background: none;
            border: none;
        }

        .select2-container--classic .select2-selection--single {
            height: 42px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.375rem !important;
            background-color: #fff !important;
            box-shadow: none !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 12px !important;
            color: #374151 !important;
            font-size: 0.875rem !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            border-left: 1px solid #e5e7eb !important;
            background: transparent !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 38px !important;
            line-height: 38px !important;
            margin-right: 27px !important;
            font-size: 25px !important;
            color: #9ca3af !important;
        }

        .select2-container--classic .select2-selection--single:focus {
            border-color: #3b82f6 !important;
            outline: none !important;
        }

        .select2-container .select2-search--inline .select2-search__field {
            height: 20px !important;
        }

        .select2-dropdown {
            border: 1px solid #e5e7eb !important;
            border-radius: 0.375rem !important;
            z-index: 9999 !important;
        }
    </style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="px-6 pt-6 pb-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('assets.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Edit Asset</h2>
                <p class="text-sm text-gray-400 mt-0.5">Assets &rsaquo; {{ $store->name }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('assets.update', $store->id) }}" class="gift-submit-form"
        enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="px-6 py-4 space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- Left Column --}}
                <div class="space-y-5">

                    {{-- ── Section 1: Classification & Assignment ── --}}
                    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                        <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                            <div class="mt-0.5 text-blue-500">
                                <i class="bi bi-diagram-3 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-800">Classification & Assignment</h3>
                                <p class="text-sm text-gray-400">Make, model, and location assignment</p>
                            </div>
                        </div>

                        <div class="px-6 grid grid-cols-1 gap-4">
                            <input name="location" id="location" type="hidden" value="{{ $store->location }}">

                            {{-- Make --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="model_type">
                                    Make <span class="text-red-500">*</span>
                                </label>
                                <select name="model_type" id="model_type" class="w-full" required>
                                    <option value=""></option>
                                    @foreach ($modelTypes as $typeRow)
                                        <option value="{{ $typeRow->id }}" @if($typeRow->id == $store->model_type) selected
                                        @endif>
                                            {{ $typeRow->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('model_type'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('model_type') }}</p>
                                @endif
                            </div>

                            {{-- Model --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="store_type">
                                    Model <span class="text-red-500">*</span>
                                </label>
                                <select name="store_type" id="store_type" class="w-full" required>
                                    <option value=""></option>
                                    @foreach ($storeTypes as $typeRow)
                                        <option value="{{ $typeRow->id }}" @if($typeRow->id == $store->store_type) selected
                                        @endif>
                                            {{ $typeRow->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('store_type'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('store_type') }}</p>
                                @endif
                            </div>

                            {{-- Category --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="store_category">
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select name="store_category" id="store_category" class="w-full" required>
                                    <option value=""></option>
                                    @foreach ($storeCategories as $category_row)
                                        <option value="{{ $category_row->id }}" @if($category_row->id == $store->store_category)
                                        selected @endif>
                                            {{ $category_row->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('store_category'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('store_category') }}</p>
                                @endif
                            </div>

                            {{-- Location --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="location_item">
                                    Location
                                </label>
                                <select name="location_item" id="location_item" class="w-full">
                                    @foreach ($locationItems as $locationItem)
                                        <option value="{{ $locationItem->id }}" selected>
                                            {{ $locationItem->code }} - {{ $locationItem->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('location_item'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('location_item') }}</p>
                                @endif
                            </div>

                            {{-- Assign To --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="dom_id">
                                    Assign To
                                </label>
                                <select name="dom_id" id="dom_id" class="w-full">
                                    @foreach ($assignTo as $assign)
                                        <option value="{{ $assign->id }}" selected>
                                            {{ $assign->employee_id }} - {{ $assign->name }} {{ $assign->middle_name }}
                                            {{ $assign->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('dom_id'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('dom_id') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 2: Identity & Codes ── --}}
                    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                        <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                            <div class="mt-0.5 text-purple-500">
                                <i class="bi bi-upc-scan text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">Identity & Codes</h3>
                                <p class="text-sm text-gray-400">Name and tracking identifiers</p>
                            </div>
                        </div>

                        <div class="px-6 grid grid-cols-1 gap-4">
                            {{-- Name --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="locname">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input name="name" type="text" id="locname"
                                    class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    value="{{ old('name', $store->name) }}" placeholder="Location Name" required>
                                @if ($errors->has('name'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('name') }}</p>
                                @endif
                            </div>

                            {{-- Unique Code --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="ucode">
                                    Unique Code <span class="text-red-500">*</span>
                                </label>
                                <input name="ucode" type="text" id="ucode"
                                    class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    value="{{ old('ucode', $store->ucode) }}" placeholder="Unique Code" required>
                                @if ($errors->has('ucode'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('ucode') }}</p>
                                @endif
                            </div>

                            {{-- Internal Code --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="loccode">
                                    Internal Code
                                </label>
                                <input name="code" type="text" id="loccode"
                                    class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    value="{{ old('code', $store->code) }}" placeholder="Location Code">
                                @if ($errors->has('code'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('code') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 3: Purchase & Lifecycle ── --}}
                    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                        <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                            <div class="mt-0.5 text-amber-500">
                                <i class="bi bi-calendar-check text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">Purchase & Lifecycle</h3>
                                <p class="text-sm text-gray-400">PO dates, warranty and status</p>
                            </div>
                        </div>

                        <div class="px-6 grid grid-cols-1 gap-4">
                            {{-- PO Date --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="po_date">
                                    PO Date <span class="text-red-500">*</span>
                                </label>
                                <input name="po_date" type="date" id="po_date"
                                    class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    value="{{ old('po_date', \Carbon\Carbon::parse($store->po_date)->format('Y-m-d')) }}"
                                    required>
                                @if ($errors->has('po_date'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('po_date') }}</p>
                                @endif
                            </div>

                            {{-- Warranty --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="warranty">
                                    Warranty (Months) <span class="text-red-500">*</span>
                                </label>
                                <input name="warranty" type="number" min="0" step="1" max="1000" id="warranty"
                                    class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    value="{{ old('warranty', $store->warranty) }}" placeholder="Warranty (Months)"
                                    required>
                                @if ($errors->has('warranty'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('warranty') }}</p>
                                @endif
                            </div>

                            {{-- Lifespan --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="lifespan">
                                    Lifespan (Months) <span class="text-red-500">*</span>
                                </label>
                                <input name="lifespan" type="number" min="0" step="1" max="1000" id="lifespan"
                                    class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    value="{{ old('lifespan', $store->lifespan) }}" placeholder="Lifespan (Months)"
                                    required>
                                @if ($errors->has('lifespan'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('lifespan') }}</p>
                                @endif
                            </div>

                            {{-- Asset Status --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="asset_status_id">
                                    Asset Status <span class="text-red-500">*</span>
                                </label>
                                <select name="asset_status_id" id="asset_status_id" class="w-full" required>
                                    @foreach ($assetStatuses as $status)
                                        <option value="{{ $status->id }}" @if ($status->id == $store->asset_status_id) selected
                                        @endif style="color: {{ $status->color }}; font-weight: bold;">
                                            {{ $status->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('asset_status_id'))
                                    <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('asset_status_id') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column --}}
                <div class="space-y-5">

                    {{-- ── Section 4: Media & Documents ── --}}
                    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                        <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                            <div class="mt-0.5 text-green-500">
                                <i class="bi bi-images text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">Media & Documents</h3>
                                <p class="text-sm text-gray-400">Attachments and images</p>
                            </div>
                        </div>

                        <div class="px-6 grid grid-cols-1 gap-4">
                            {{-- Primary Image --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="primary_image">
                                    Primary Image
                                </label>
                                <input type="file" class="filepond" name="primary_image" id="primary_image"
                                    data-max-file-size="3MB">
                                @if ($store->primary_image)
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-100 flex gap-4 items-center">
                                        <img src="{{ asset('storage/assets-images/' . $store->primary_image) }}"
                                            alt="Primary Image" class="w-20 h-20 object-cover rounded shadow-sm">
                                        <div class="text-xs text-gray-500">Current Primary Image</div>
                                    </div>
                                @endif
                            </div>

                            {{-- Secondary Images --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="secondary_images">
                                    Secondary Images
                                </label>
                                <input type="file" class="filepond" name="secondary_images[]" id="secondary_images" multiple
                                    data-max-file-size="3MB">

                                @if ($store->secondary_images)
                                    <div class="mt-3">
                                        <p class="text-xs text-gray-500 mb-2">Current Secondary Images:</p>
                                        <div class="flex flex-wrap gap-3">
                                            @foreach ($store->secondary_images as $image)
                                                <div class="relative group mt-1">
                                                    <img src="{{ asset('storage/assets-images/' . $image) }}" alt="Secondary Image"
                                                        class="w-20 h-20 object-cover rounded shadow-sm border border-gray-200">

                                                    <div class="flex items-center gap-1 mt-1">
                                                        <input type="checkbox" name="remove_secondary_images[]" value="{{ $image }}"
                                                            id="remove_{{ $loop->index }}"
                                                            class="rounded text-red-500 focus:ring-red-500">
                                                        <label for="remove_{{ $loop->index }}"
                                                            class="text-xs text-red-500 cursor-pointer">Remove</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Documents --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="documents">
                                    Documents
                                </label>
                                <input type="file" class="filepond" name="documents[]" id="documents" multiple
                                    data-max-file-size="10MB">
                                <small class="text-gray-500 block mb-1">Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max
                                    10MB each)</small>
                                <div id="document-title-wrap" class="mt-2 space-y-2"></div>
                                @error('document_titles')
                                    <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                                @enderror

                                @if ($store->documents)
                                    <div class="mt-4">
                                        <p class="text-xs font-semibold text-gray-600 mb-2">Current Documents:</p>
                                        <div class="space-y-2">
                                            @foreach ($store->documents as $index => $document)
                                                <div
                                                    class="doc-title-row flex items-center gap-2 p-2 border border-[#e5e7eb] rounded-lg bg-gray-50">
                                                    <a href="{{ asset('storage/asset-documents/' . $document) }}" target="_blank"
                                                        class="flex-1 text-xs text-blue-600 truncate hover:underline flex items-center gap-2"
                                                        title="{{ $document }}">
                                                        <i class="bi bi-file-earmark"></i> {{ $document }}
                                                    </a>
                                                    <input type="text" name="existing_document_titles[{{ $document }}]"
                                                        class="w-1/3 h-[34px] px-3 text-sm border border-[#e5e7eb] rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                        value="{{ old('existing_document_titles', [])[$document] ?? $store->getDocumentTitleByFile($document) }}"
                                                        placeholder="Document title">

                                                    <div
                                                        class="flex items-center gap-1.5 text-xs text-red-500 bg-red-50 px-2 py-1.5 rounded-md border border-red-100">
                                                        <input type="checkbox" name="remove_documents[]" value="{{ $document }}"
                                                            id="remove_doc_{{ $index }}"
                                                            class="rounded text-red-500 focus:ring-red-500">
                                                        <label for="remove_doc_{{ $index }}" class="cursor-pointer">Remove</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 5: Linked Assets ── --}}
                    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-4">
                        <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                            <div class="mt-0.5 text-blue-500">
                                <i class="bi bi-link-45deg text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">Linked Assets</h3>
                                <p class="text-sm text-gray-400">Associate other assets structurally</p>
                            </div>
                        </div>

                        <div class="px-6">
                            <div class="overflow-x-auto mb-3">
                                <table class="w-full text-left border-collapse" id="assets-table">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="py-2.5 px-3 text-sm font-medium text-gray-700 w-[45%]">Asset</th>
                                            <th class="py-2.5 px-3 text-sm font-medium text-gray-700 w-[45%]">Description
                                            </th>
                                            <th class="py-2.5 px-3 text-sm font-medium text-gray-700 w-[10%] text-center">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($locationAssets as $index => $locationAsset)
                                            @if (isset($locationAsset->asset->id))
                                                <tr id="asset-row-{{ $index }}" class="border-b border-gray-50">
                                                    <td class="p-2">
                                                        <select name="assets[{{ $index }}][asset_id]"
                                                            class="w-full select2 asset-select" required>
                                                            <option value="{{ $locationAsset->asset_id }}" selected>
                                                                {{ $locationAsset->asset->code }} -
                                                                {{ $locationAsset->asset->name }}
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td class="p-2">
                                                        <input type="text" name="assets[{{ $index }}][description]"
                                                            class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                            placeholder="Description" value="{{ $locationAsset->description }}">
                                                    </td>
                                                    <td class="p-2 text-center">
                                                        <button type="button"
                                                            class="w-[38px] h-[38px] inline-flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors remove-asset-btn"
                                                            data-row-id="{{ $index }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="add-asset-btn"
                                class="inline-flex items-center justify-center gap-2 w-full py-2.5 border-1.5 border-dashed border-[#c5cbe0] hover:border-blue-500 hover:bg-blue-50 text-gray-500 hover:text-blue-600 rounded-lg text-sm font-medium transition-colors">
                                <i class="bi bi-plus-lg"></i> Add Asset
                            </button>
                        </div>
                    </div>

                    {{-- ── Section 6: Asset Labels ── --}}
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
                        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6 mt-5">
                            <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                                <div class="mt-0.5 text-purple-600">
                                    <i class="bi bi-upc-scan text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800">Asset Labels</h3>
                                    <p class="text-sm text-gray-400">Download tracking codes</p>
                                </div>
                            </div>

                            <div class="px-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if ($qrCodeImageUrl)
                                    <div
                                        class="p-4 border border-gray-100 rounded-xl bg-gray-50 text-center flex flex-col items-center">
                                        <p class="text-sm font-medium text-gray-600 mb-3">QR Code</p>
                                        <div class="bg-white p-2 rounded shadow-sm border border-gray-100 mb-4 inline-block">
                                            <img src="{{ $qrCodeImageUrl }}" alt="QR Code" class="h-28">
                                        </div>
                                        <a href="{{ $qrCodeImageUrl }}" download="{{ $store->ucode }}-qr.png"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm border-1.5 border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors font-medium w-full">
                                            <i class="bi bi-download"></i> Download QR
                                        </a>
                                    </div>
                                @endif
                                @if ($barcodeImageUrl)
                                    <div
                                        class="p-4 border border-gray-100 rounded-xl bg-gray-50 text-center flex flex-col items-center">
                                        <p class="text-sm font-medium text-gray-600 mb-3">Barcode</p>
                                        <div class="bg-white p-2 rounded shadow-sm border border-gray-100 mb-4 inline-block">
                                            <img src="{{ $barcodeImageUrl }}" alt="Barcode" class="h-28">
                                        </div>
                                        <a href="{{ $barcodeImageUrl }}" download="{{ $store->ucode }}-barcode.png"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm border-1.5 border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors font-medium w-full">
                                            <i class="bi bi-download"></i> Download Barcode
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- ── Submit ── --}}
            <div class="flex justify-end pb-4 mt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-check2-circle"></i>
                    Update Asset
                </button>
            </div>

        </div>
    </form>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
        integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

    <script>
        $(document).ready(function () {
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
                files.forEach(function (fileItem, index) {
                    const fileName = fileItem.file && fileItem.file.name ? fileItem.file.name : (
                        'Document ' + (index + 1));
                    documentTitleWrap.append(`
                            <div class="flex items-center gap-2 p-2 border border-[#e5e7eb] rounded-lg mt-2 bg-gray-50">
                                <i class="bi bi-file-earmark-text text-gray-400"></i>
                                <div class="text-xs text-gray-600 truncate flex-1" title="${fileName}">${fileName}</div>
                                <input type="text" name="document_titles[]" class="h-[34px] px-3 text-sm border border-[#e5e7eb] rounded focus:outline-none focus:ring-1 focus:ring-blue-500 w-1/2" placeholder="Document title (required)" required>
                            </div>
                        `);
                });
            }
            pond3.on('addfile', renderDocumentTitleInputs);
            pond3.on('removefile', renderDocumentTitleInputs);

            $('.gift-submit-form').on('submit', function (e) {
                const seen = [];
                let hasError = false;

                $('input[name="document_titles[]"], input[name^="existing_document_titles["]').removeClass(
                    'border-red-500 focus:ring-red-500');

                $('input[name^="existing_document_titles["]').each(function () {
                    const container = $(this).closest('.doc-title-row');
                    const removeCheck = container.find('input[name="remove_documents[]"]');
                    if (removeCheck.length && removeCheck.is(':checked')) {
                        return;
                    }
                    const val = String($(this).val() || '').trim().toLowerCase();
                    if (!val || seen.includes(val)) {
                        $(this).addClass('border-red-500 focus:ring-red-500');
                        hasError = true;
                    } else {
                        seen.push(val);
                    }
                });

                $('input[name="document_titles[]"]').each(function () {
                    const val = String($(this).val() || '').trim().toLowerCase();
                    if (!val || seen.includes(val)) {
                        $(this).addClass('border-red-500 focus:ring-red-500');
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
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function (item) {
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
                templateResult: function (data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function () { });

            $('#filter_dom, #dom_id').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            roles: "{{ implode(',', [Helper::$roles['divisional-operations-manager']]) }}",
                            getall: true
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function (item) {
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
                templateResult: function (data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function () {

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

            $('#add-asset-btn').on('click', function () {
                let html = `
                        <tr id="asset-row-${assetRowIndex}" class="border-b border-gray-50">
                            <td class="p-2">
                                <select name="assets[${assetRowIndex}][asset_id]" class="w-full" required></select>
                            </td>
                            <td class="p-2">
                                <input type="text" name="assets[${assetRowIndex}][description]" class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Description (Optional)">
                            </td>
                            <td class="p-2 text-center">
                                <button type="button" class="w-[38px] h-[38px] inline-flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors remove-asset-btn" data-row-id="${assetRowIndex}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                $('#assets-table tbody').append(html);

                initializeAssetSelect2(assetRowIndex);
                assetRowIndex++;
            });

            $(document).on('click', '.remove-asset-btn', function () {
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
                        data: function (params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                _token: "{{ csrf_token() }}",
                                unassigned_only: 1,
                                assets: 1
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: $.map(data.items, function (item) {
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

            // Re-bind validation scripts...
            jQuery.validator.addMethod("extension", function (value, element, param) {
                if (element.files.length > 0) {
                    const file = element.files[0];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    return fileExtension === param.toLowerCase();
                }
                return true;
            }, "Please upload a valid file type.");

            jQuery.validator.addMethod("filesize", function (value, element, param) {
                if (element.files.length > 0) {
                    return element.files[0].size <= param;
                }
                return true;
            }, "File size must not exceed {0} bytes.");

        });
    </script>
@endpush