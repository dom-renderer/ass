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
        body {
            font-family: 'DynamicAppFont', sans-serif !important;
        }

        /* keep Select2 consistent inside Tailwind UI */
        [data-als] .select2-container--classic .select2-selection--single,
        [data-als] .select2-container--classic .select2-selection--multiple {
            border: 1px solid rgb(226 232 240) !important;
            border-radius: 0.75rem !important;
            min-height: 42px !important;
            background: #fff !important;
        }

        [data-als] .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 12px !important;
        }

        [data-als] .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }

        .select2-container .select2-search--inline .select2-search__field {
            height: 20px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 38px !important;
            line-height: 38px !important;
            margin-right: 27px !important;
            font-size: 25px !important;
        }

        .select2-container {
            background: none;
            border: none;
        }
    </style>
@endpush

@section('content')

    <div data-als class="px-6 py-6">
        <div>
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-5">
                <div>
                    <div class="text-gray-900 text-2xl font-semibold tracking-tight">Assets</div>
                    <div class="text-gray-500 text-sm mt-0.5">
                        {{ $stores->total() }} total &bull; Showing
                        {{ $stores->firstItem() ?? 0 }}&ndash;{{ $stores->lastItem() ?? 0 }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-300"
                        id="export-stores">
                        <i class="bi bi-file-earmark-excel text-lg"></i>
                        Export Excel
                    </button>
                    <a href="{{ route('assets.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-black px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <i class="bi bi-plus-lg text-lg"></i>
                        Add Asset
                    </a>
                </div>
            </div>

            <form action="{{ route('assets.index') }}" method="GET" class="mb-6">
                <div class="rounded-xl border border-[#e5e7eb] bg-white shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-gray-800 font-semibold text-sm">
                                <span
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                    <i class="bi bi-funnel"></i>
                                </span>
                                Filters
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black transition-colors shadow-sm">
                                    <i class="bi bi-search"></i>
                                    Search
                                </button>
                                <a href="{{ route('assets.index') }}"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors @if(empty($locationFilter) && empty($domFilter) && empty(request('filter_code')) && empty(request('filter_ucode')) && empty(request('filter_name')) && empty(request('filter_status'))) hidden @endif">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    Clear
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-5">
                            <div class="lg:col-span-2">
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
                                    name="filter_code" id="filter_code" placeholder="Code"
                                    value="{{ request('filter_code') }}">
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
                                <label for="filter_location"
                                    class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                                <select name="filter_location" id="filter_location" class="w-full">
                                    @if(!empty(request('filter_location')) && request('filter_location') != 'all')
                                        @if(!empty($locationFilter))
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
                                    @if(!empty(request('filter_dom')) && request('filter_dom') != 'all')
                                        @if(!empty($domFilter))
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
                                <label for="filter_status"
                                    class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                                <select name="filter_status" id="filter_status" class="w-full">
                                    <option value="all">All</option>
                                    @foreach ($assetStatuses as $status)
                                        <option value="{{ $status->id }}" @if(request('filter_status') == $status->id) selected
                                        @endif> {{ $status->title }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @forelse ($stores as $branch)
                            @php
                                $toExpireIn = !empty($branch->po_date) ? \Carbon\Carbon::parse($branch->po_date)->addMonths((int) ($branch->lifespan ?? 0)) : null;
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
                                            @if(isset($branch->assetStatus))
                                                <span
                                                    class="als-status-badge inline-flex items-center rounded bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800 border border-gray-200"
                                                    data-bg="{{ $branch->assetStatus->color }}">
                                                    {{ $branch->assetStatus->title }}
                                                </span>
                                            @endif

                                            @if($expiry !== null)
                                                @if($expiry >= 1 && $expiry <= 60)
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
                                                {{ isset($branch->dom) ? ($branch->dom->employee_id . ' - ' . $branch->dom->name) : 'Unassigned' }}
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between text-sm pt-2">
                                            <div class="font-medium text-gray-500">QR Label</div>
                                            <div>
                                                @if($branch->scan_links['qr_code_label'] ?? null)
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
                                                @if($branch->scan_links['barcode_label'] ?? null)
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
                    <div class="md:col-span-2 xl:col-span-3">
                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center">
                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-gray-400 shadow-sm border border-gray-200">
                                <i class="bi bi-box-seam text-2xl"></i>
                            </div>
                            <div class="mt-4 text-gray-900 font-semibold text-lg">No assets found</div>
                            <div class="mt-1 text-gray-500 text-sm">Try adjusting filters or add a new asset to your system.
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-6 flex justify-center">
                {{ $stores->links() }}
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>

    <script>
        $(document).ready(function () {

            $(document).on('click', '.deleteGroup', function (e) {
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

            $('#export-stores').on('click', function () {
                $.ajax({
                    url: "{{ route('export-assets') }}",
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
                            download: "assets.xlsx"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });

            // Apply dynamic status colors uniquely
            $('[data-als] .als-status-badge').each(function () {
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
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
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
            });

        });
    </script>
@endpush