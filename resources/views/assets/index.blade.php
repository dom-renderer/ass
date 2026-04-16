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
                <h2 class="text-2xl font-semibold text-gray-800">Assets</h2>
                <p class="text-sm text-gray-400 mt-0.5">Manage all assets</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="export-stores"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-[#e5e7eb] bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-cloud-arrow-down"></i>
                    Export
                </button>
                <a href="{{ route('assets.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    Add Asset
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
                    <div>
                        <label for="filter_name" class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                        <input type="text"
                            class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
                            name="filter_name" id="filter_name" placeholder="Search by name"
                            value="{{ request('filter_name') }}">
                    </div>
                    <div>
                        <label for="filter_code" class="block text-sm font-medium text-gray-700 mb-1.5">Code</label>
                        <input type="text"
                            class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
                            name="filter_code" id="filter_code" placeholder="Code" value="{{ request('filter_code') }}">
                    </div>
                    <div>
                        <label for="filter_ucode" class="block text-sm font-medium text-gray-700 mb-1.5">Unique
                            Code</label>
                        <input type="text"
                            class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
                            name="filter_ucode" id="filter_ucode" placeholder="Unique Code"
                            value="{{ request('filter_ucode') }}">
                    </div>
                    <div>
                        <label for="filter_location" class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                        <select name="filter_location" id="filter_location" class="w-full">
                            @if (!empty(request('filter_location')) && request('filter_location') != 'all')
                                @if (!empty($locationFilter))
                                    <option value="{{ request('filter_location') }}"> {{ $locationFilter->name ?? '' }}
                                    </option>
                                @endif
                            @else
                                <option value="all" selected> All </option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label for="filter_dom" class="block text-sm font-medium text-gray-700 mb-1.5">User</label>
                        <select name="filter_dom" id="filter_dom" class="w-full">
                            @if (!empty(request('filter_dom')) && request('filter_dom') != 'all')
                                @if (!empty($domFilter))
                                    <option value="{{ request('filter_dom') }}"> {{ $domFilter->employee_id ?? '' }} -
                                        {{ $domFilter->name ?? '' }} {{ $domFilter->middle_name ?? '' }}
                                        {{ $domFilter->last_name ?? '' }} </option>
                                @endif
                            @else
                                <option value="all" selected> All </option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select name="filter_status" id="filter_status" class="w-full">
                            <option value="all">All</option>
                            @foreach ($assetStatuses as $status)
                                <option value="{{ $status->id }}" @if (request('filter_status') == $status->id) selected @endif>
                                    {{ $status->title }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div
                    class="px-6 py-4 mt-2 flex items-center justify-end gap-3 bg-gray-50 rounded-b-xl border-t border-gray-100">
                    <a href="{{ route('stores.index') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 border border-[#e5e7eb] bg-white rounded-lg hover:bg-gray-50 transition-colors shadow-sm @if (empty($stateFilter) &&
                                empty($cityFilter) &&
                                empty($domFilter) &&
                                empty(request('filter_status')) &&
                                empty(request('internal_code')) &&
                                empty(request('filter_name')) &&
                                empty(request('filter_ucode'))) hidden @endif">
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
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse ($stores as $branch)
                @php
                    $toExpireIn = !empty($branch->po_date)
                        ? \Carbon\Carbon::parse($branch->po_date)->addMonths((int) ($branch->lifespan ?? 0))
                        : null;
                    $expiry = $toExpireIn ? $toExpireIn->diffInDays(\Carbon\Carbon::now(), false) : null;
                @endphp

                <div
                    class="rounded-xl border border-[#e5e7eb] bg-white shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-gray-900 font-semibold text-[17px] leading-snug truncate">
                                    {{ $branch->name }}
                                </div>
                                <div class="text-gray-500 text-sm mt-1 flex items-center gap-1.5">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $branch->store->name ?? 'Unassigned' }}
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 shrink-0">
                                @if (isset($branch->assetStatus))
                                    <span
                                        class="als-status-badge inline-flex items-center rounded bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800 border border-gray-200"
                                        data-bg="{{ $branch->assetStatus->color }}">
                                        {{ $branch->assetStatus->title }}
                                    </span>
                                @endif

                                @if ($expiry !== null)
                                    @if ($expiry >= 1 && $expiry <= 60)
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 border border-amber-200">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Near Expiry ({{ $toExpireIn ? $toExpireIn->format('d-m-Y') : '' }})
                                        </span>
                                    @elseif($expiry <= 0)
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 border border-red-200">
                                            <i class="bi bi-x-circle"></i>
                                            Expired
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 space-y-2.5 pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between text-sm">
                                <div class="font-medium text-gray-500">Unique Code</div>
                                <div class="font-semibold text-gray-800">{{ $branch->ucode }}</div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="font-medium text-gray-500">Code</div>
                                <div class="font-semibold text-gray-800">{{ $branch->code ?: '-' }}</div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="font-medium text-gray-500">User Assignee</div>
                                <div class="text-gray-800 font-medium truncate max-w-[60%]">
                                    {{ isset($branch->dom) ? $branch->dom->employee_id . ' - ' . $branch->dom->name : 'Unassigned' }}
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm pt-2">
                                <div class="font-medium text-gray-500">QR Label</div>
                                <div>
                                    @if ($branch->scan_links['qr_code_label'] ?? null)
                                        <a class="text-blue-600 hover:text-blue-800 font-medium" target="_blank"
                                            href="{{ $branch->scan_links['qr_code_label'] }}">
                                            View
                                        </a>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="font-medium text-gray-500">Barcode Label</div>
                                <div>
                                    @if ($branch->scan_links['barcode_label'] ?? null)
                                        <a class="text-blue-600 hover:text-blue-800 font-medium" target="_blank"
                                            href="{{ $branch->scan_links['barcode_label'] }}">
                                            View
                                        </a>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-100 rounded-b-xl flex gap-2">
                        <a href="{{ route('assets.edit', $branch->id) }}"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors shadow-sm">
                            <i class="bi bi-pencil-square"></i>
                            Edit
                        </a>

                        {!! Form::open([
                            'method' => 'DELETE',
                            'route' => ['assets.destroy', $branch->id],
                            'class' => 'w-1/2',
                        ]) !!}
                        <button type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100 transition-colors deleteGroup">
                            <i class="bi bi-trash"></i>
                            Delete
                        </button>
                        {!! Form::close() !!}
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 bg-white rounded-xl border border-dashed border-[#e5e7eb] text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                        <i class="bi bi-search text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No Assets Found</h3>
                    <p class="text-sm text-gray-500 mb-4">Try adjusting your filters or add a new asset to get started.
                    </p>
                    <a href="{{ route('assets.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-[#e5e7eb] bg-white rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        <i class="bi bi-plus-lg"></i> Add New Asset
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8 flex justify-center">
            {{ $stores->links('pagination::bootstrap-4') }}
        </div>

    </div>

@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>

    <script>
        $(document).ready(function() {

            $(document).on('click', '.deleteGroup', function(e) {
                e.preventDefault();
                const $btn = $(this);
                Swal.fire({
                    title: 'Are you sure you want to delete this Asset?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $btn.parents('form').submit();
                        return true;
                    }
                    return false;
                })
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
                            download: "assets.xlsx"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });

            // Apply dynamic status colors uniquely
            $('[data-als] .als-status-badge').each(function() {
                const bg = $(this).data('bg');
                if (bg) {
                    this.style.backgroundColor = bg;
                    this.style.color = '#fff';
                }
            });

            // Select2
            $('#filter_status').select2({
                placeholder: 'Select Status',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#filter_location').select2({
                placeholder: 'Select Location',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
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
            });

            $('#filter_dom').select2({
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
            });

        });
    </script>
@endpush
