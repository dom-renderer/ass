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
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />

    <style type="text/css">
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

        /* Modal Backdrop */
        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }
    </style>
@endpush

@section('content')
    <div class="px-6 pt-6 pb-4">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Locations</h2>
                <p class="text-sm text-gray-400 mt-0.5">Manage all locations</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-colors"
                    data-bs-toggle="modal" data-bs-target="#browser-file">
                    <i class="bi bi-cloud-arrow-up"></i>
                    Import
                </button>
                <button type="button" id="export-stores"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-[#e5e7eb] bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-cloud-arrow-down"></i>
                    Export
                </button>
                <a href="{{ route('stores.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    Add Location
                </a>
            </div>
        </div>

        {{-- Filters Card --}}
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm mb-6 pb-2">
            <form action="{{ route('stores.index') }}" method="GET">
                <div class="px-6 py-4 border-b border-gray-100 mb-2">
                    <div class="flex items-center gap-2 text-gray-800">
                        <i class="bi bi-funnel text-gray-500"></i>
                        <h3 class="text-sm font-semibold">Filter Locations</h3>
                    </div>
                </div>

                <div class="px-6 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {{-- Filter: Name --}}
                    <div>
                        <label for="filter_name" class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                        <input type="text" name="filter_name" id="filter_name" placeholder="Location Name"
                            class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                            value="{{ request('filter_name') }}">
                    </div>

                    {{-- Filter: Code --}}
                    <div>
                        <label for="filter_location" class="block text-sm font-medium text-gray-700 mb-1.5">Code</label>
                        <input type="text" name="filter_location" id="filter_location" placeholder="Location Code"
                            class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                            value="{{ request('filter_location') }}">
                    </div>

                    {{-- Filter: Unique Code --}}
                    <div>
                        <label for="filter_ucode" class="block text-sm font-medium text-gray-700 mb-1.5">Unique Code</label>
                        <input type="text" name="filter_ucode" id="filter_ucode" placeholder="Unique Code"
                            class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                            value="{{ request('filter_ucode') }}">
                    </div>

                    {{-- Filter: State --}}
                    <div>
                        <label for="filter_state" class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                        <select name="filter_state" id="filter_state" class="w-full">
                            @if(!empty(request('filter_state')) && request('filter_state') != 'all')
                                @if(!empty($stateFilter))
                                    <option value="{{ request('filter_state') }}" selected>{{ $stateFilter->city_state }}</option>
                                @endif
                            @else
                                <option value="all" selected>All</option>
                            @endif
                        </select>
                    </div>

                    {{-- Filter: City --}}
                    <div>
                        <label for="filter_city" class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                        <select name="filter_city" id="filter_city" class="w-full">
                            @if(!empty(request('filter_city')) && request('filter_city') != 'all')
                                @if(!empty($cityFilter))
                                    <option value="{{ request('filter_city') }}" selected>{{ $cityFilter->city_name }}</option>
                                @endif
                            @else
                                <option value="all" selected>All</option>
                            @endif
                        </select>
                    </div>

                    {{-- Filter: DoM --}}
                    <div>
                        <label for="filter_dom" class="block text-sm font-medium text-gray-700 mb-1.5">DOM</label>
                        <select name="filter_dom" id="filter_dom" class="w-full">
                            @if(!empty(request('filter_dom')) && request('filter_dom') != 'all')
                                @if(!empty($domFilter))
                                    <option value="{{ request('filter_dom') }}" selected>
                                        {{ $domFilter->employee_id }} - {{ $domFilter->name }} {{ $domFilter->middle_name }}
                                        {{ $domFilter->last_name }}
                                    </option>
                                @endif
                            @else
                                <option value="all" selected>All</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div
                    class="px-6 py-4 mt-2 flex items-center justify-end gap-3 bg-gray-50 rounded-b-xl border-t border-gray-100">
                    <a href="{{ route('stores.index') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 border border-[#e5e7eb] bg-white rounded-lg hover:bg-gray-50 transition-colors shadow-sm @if(empty($stateFilter) && empty($cityFilter) && empty($domFilter) && empty(request('filter_location')) && empty(request('filter_name')) && empty(request('filter_ucode'))) hidden @endif">
                        Clear Filters
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg hover:bg-gray-900 transition-colors shadow-sm">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- Data Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse ($stores as $branch)
                <div
                    class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col">
                    {{-- Card Header --}}
                    <div class="p-5 border-b border-gray-100 relative">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 leading-tight mb-1">{{ $branch->name }}</h3>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if(isset($branch->modeltype->id))
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $branch->modeltype->name }}
                                        </span>
                                    @endif
                                    @if(isset($branch->storetype->id))
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ $branch->storetype->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</span>
                                <span class="block text-sm font-medium text-gray-900">{{ $branch->code }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-5 flex-1 space-y-4">
                        {{-- Contact Block --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div
                                    class="flex items-center gap-1.5 text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                    <i class="bi bi-geo-alt"></i> Address
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $branch->address1 }}</p>
                                @if($branch->address2 || $branch->street || $branch->block)
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ implode(', ', array_filter([$branch->address2, $branch->block, $branch->street])) }}
                                    </p>
                                @endif
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ isset($branch->thecity->city_name) ? $branch->thecity->city_name . ', ' : '' }}{{ isset($branch->thecity->city_state) ? $branch->thecity->city_state : '' }}
                                </p>
                            </div>
                            <div>
                                <div
                                    class="flex items-center gap-1.5 text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                    <i class="bi bi-clock"></i> Timings
                                </div>
                                <div class="text-sm text-gray-700 space-y-0.5">
                                    <p><span class="text-gray-400">Open:</span> {{ $branch->open_time }}</p>
                                    <p><span class="text-gray-400">Close:</span> {{ $branch->close_time }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-50"></div>

                        {{-- Contact info --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div
                                    class="flex items-center gap-1.5 text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                    <i class="bi bi-telephone"></i> Contact
                                </div>
                                <div class="space-y-1">
                                    @if($branch->mobile)
                                        <div class="flex items-center gap-2 text-sm text-gray-700">
                                            <i class="bi bi-phone text-gray-400"></i> {{ $branch->mobile }}
                                        </div>
                                    @endif
                                    @if($branch->email)
                                        <div class="flex items-center gap-2 text-sm text-gray-700">
                                            <i class="bi bi-envelope text-gray-400"></i> <a href="mailto:{{ $branch->email }}"
                                                class="text-blue-600 hover:underline">{{ Str::limit($branch->email, 20) }}</a>
                                        </div>
                                    @endif
                                    @if($branch->whatsapp)
                                        <div class="flex items-center gap-2 text-sm text-gray-700">
                                            <i class="bi bi-whatsapp text-green-500"></i> {{ $branch->whatsapp }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div
                                    class="flex items-center gap-1.5 text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                    <i class="bi bi-person-badge"></i> Details
                                </div>
                                <div class="space-y-1 text-sm text-gray-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400">Unique:</span>
                                        <span class="font-medium">{{ $branch->ucode }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400">DOM:</span>
                                        <span class="text-right">{{ isset($branch->dom) ? $branch->dom->name : 'N/A' }}</span>
                                    </div>
                                    @if ($branch->location && $branch->location != 'location')
                                        <div class="pt-1">
                                            <a href="{{ $branch->location_url ?? 'javascript:void(0);' }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 px-2 py-1 rounded">
                                                <i class="bi bi-pin-map-fill"></i> View on Map
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(isset($branch->scan_links['qr_code_label']) || isset($branch->scan_links['barcode_label']))
                            <div class="border-t border-gray-50"></div>
                            <div class="flex flex-wrap gap-2">
                                @if(isset($branch->scan_links['qr_code_label']))
                                    <a target="_blank" href="{{ $branch->scan_links['qr_code_label'] }}"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors">
                                        <i class="bi bi-qr-code"></i> Print QR Label
                                    </a>
                                @endif
                                @if(isset($branch->scan_links['barcode_label']))
                                    <a target="_blank" href="{{ $branch->scan_links['barcode_label'] }}"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors">
                                        <i class="bi bi-upc-scan"></i> Barcode
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Card Footer / Actions --}}
                    <div
                        class="bg-gray-50 p-3 mt-auto border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            @can('store_qr_codes.view')
                                <a href="{{ route('locations.qr-codes.index', $branch->id) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-[#e5e7eb] rounded hover:bg-gray-50 transition-colors shadow-sm">
                                    QR Codes
                                </a>
                            @endcan
                            @can('store_menu.manage')
                                <a href="{{ route('locations.menu-assignment.index', $branch->id) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 justify-center border border-green-200 rounded hover:bg-green-100 transition-colors shadow-sm">
                                    Assign Menu
                                </a>
                            @endcan
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('stores.edit', $branch->id) }}"
                                class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors rounded hover:bg-blue-50"
                                title="Edit">
                                <i class="bi bi-pencil-square text-lg"></i>
                            </a>

                            {!! Form::open(['method' => 'DELETE', 'route' => ['stores.destroy', $branch->id], 'class' => 'inline-block m-0 p-0']) !!}
                            <button type="submit"
                                class="p-1.5 text-gray-400 hover:text-red-600 transition-colors rounded hover:bg-red-50 deleteGroup"
                                title="Delete">
                                <i class="bi bi-trash3 text-lg"></i>
                            </button>
                            {!! Form::close() !!}
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-12 bg-white rounded-xl border border-dashed border-[#e5e7eb] text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                        <i class="bi bi-search text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No Locations Found</h3>
                    <p class="text-sm text-gray-500 mb-4">Try adjusting your filters or add a new location to get started.</p>
                    <a href="{{ route('stores.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-[#e5e7eb] bg-white rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        <i class="bi bi-plus-lg"></i> Add New Location
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8 flex justify-center">
            {{ $stores->links('pagination::bootstrap-4') }}
        </div>

    </div>

    {{-- Import Modal --}}
    <div class="modal fade" id="browser-file" tabindex="-1" aria-labelledby="browser-file-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="fileUploader" method="POST" action="{{ route('import-stores') }}" enctype="multipart/form-data"
                class="modal-content rounded-xl border-0 shadow-xl overflow-hidden bg-white">
                @csrf

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h5 class="text-base font-semibold text-gray-800" id="browser-file-label">Import Locations</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors"
                        data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-5">
                    <div class="mb-4">
                        <label for="xlsx" class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-2">
                            <i class="bi bi-file-earmark-spreadsheet text-blue-500"></i> Select Excel File (*.xlsx)
                        </label>
                        <div class="relative">
                            <input type="file" name="xlsx" id="xlsx"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer border border-[#e5e7eb] rounded-lg p-1">
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <button type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-600 border border-[#e5e7eb] bg-white rounded-lg hover:bg-gray-100 transition-colors shadow-sm"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm inline-flex items-center gap-2">
                        <i class="bi bi-upload"></i> Process Import
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
        integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>

    <script>
        jQuery(document).ready(function ($) {
            $('#filter_state').select2({
                placeholder: 'Select State',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(data.items, function (item) {
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                },
                templateResult: function (data) {
                    if (data.loading) return data.text;
                    return $('<span></span>').text(data.text);
                }
            }).on('change', function () {
                $('#filter_city').val(null).trigger('change');
            });

            $('#filter_city').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function () { return $('#filter_state').val(); },
                            getall: true
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(data.items, function (item) {
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                },
                templateResult: function (data) {
                    if (data.loading) return data.text;
                    return $('<span></span>').text(data.text);
                }
            });

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
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                },
                templateResult: function (data) {
                    if (data.loading) return data.text;
                    return $('<span></span>').text(data.text);
                }
            });

            // File Uploader Validation & Submit
            jQuery.validator.addMethod("extension", function (value, element, param) {
                if (element.files.length > 0) {
                    const file = element.files[0];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    return fileExtension === param.toLowerCase();
                }
                return true;
            }, "Please upload a valid file type.");

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
                submitHandler: function (form, event) {
                    event.preventDefault();

                    let formData = new FormData(form);

                    $.ajax({
                        url: "{{ route('import-stores') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function (response) {
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

            $('#export-stores').on('click', function () {
                $.ajax({
                    url: "{{ route('export-stores') }}",
                    type: 'GET',
                    cache: false,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    data: getQueryParams(),
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        var url = window.URL || window.webkitURL;
                        var objectUrl = url.createObjectURL(response);
                        var a = $("<a />", {
                            href: objectUrl,
                            download: "stores.xlsx"
                        }).appendTo("body");
                        a[0].click();
                        a.remove();
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });

            $('.deleteGroup').on('click', function (e) {
                if (!confirm('Are you sure you want to delete this location?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush