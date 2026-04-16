@extends('layouts.app-master')

@push('css')
<script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />

<style type="text/css">
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
    }

    [data-als] .select2-container--classic .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

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

    .wrapper .title {
        flex-basis: 30%;
        word-break: break-all;
    }

    .wrapper .description {
        flex-basis: 70%;
        padding: 5px;
        word-break: break-word;
    }

    .row.export-button {
        padding-bottom: 30px;
    }
</style>
@endpush

@section('content')

<div data-als class="px-2 sm:px-4 md:px-6 lg:px-8">
    <div class="">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
            <div>
                <div class="text-slate-900 text-2xl font-semibold tracking-tight">Assets</div>
                <div class="text-slate-500 text-sm mt-0.5">
                    {{ $stores->total() }} total • Showing {{ $stores->firstItem() ?? 0 }}–{{ $stores->lastItem() ?? 0 }}
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300"
                    id="export-stores">
                    <i class="bi bi-file-earmark-excel"></i>
                    Export Excel
                </button>
                <a href="{{ route('assets.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl btn-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <i class="bi bi-plus-lg"></i>
                    Add Asset
                </a>
            </div>
        </div>

        <form action="{{ route('assets.index') }}" method="GET" class="mb-5">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="px-4 py-4 sm:px-6 border-b border-slate-200">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-slate-900 font-semibold">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                                <i class="bi bi-funnel"></i>
                            </span>
                            Filters
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
                                <i class="bi bi-search"></i>
                                Search
                            </button>
                            <a href="{{ route('assets.index') }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 @if(empty($locationFilter) && empty($domFilter) && empty(request('filter_code')) && empty(request('filter_ucode')) && empty(request('filter_name')) && empty(request('filter_status'))) hidden @endif">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-4 sm:px-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div class="lg:col-span-2">
                            <label for="filter_name" class="block text-sm font-semibold text-slate-700">Name</label>
                            <input type="text"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-200"
                                name="filter_name" id="filter_name" placeholder="Search by name"
                                @if(!empty(request('filter_name'))) value="{{ request('filter_name') }}" @endif>
                        </div>
                        <div>
                            <label for="filter_code" class="block text-sm font-semibold text-slate-700">Code</label>
                            <input type="text"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-200"
                                name="filter_code" id="filter_code" placeholder="Code"
                                @if(!empty(request('filter_code'))) value="{{ request('filter_code') }}" @endif>
                        </div>
                        <div>
                            <label for="filter_ucode" class="block text-sm font-semibold text-slate-700">Unique Code</label>
                            <input type="text"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:ring-indigo-200"
                                name="filter_ucode" id="filter_ucode" placeholder="Unique Code"
                                @if(!empty(request('filter_ucode'))) value="{{ request('filter_ucode') }}" @endif>
                        </div>
                        <div>
                            <label for="filter_location" class="block text-sm font-semibold text-slate-700">Location</label>
                            <select name="filter_location" id="filter_location">
                                @if(!empty(request('filter_location')) && request('filter_location') != 'all')
                                    @if(!empty($locationFilter))
                                        <option value="{{ request('filter_location') }}"> {{ $locationFilter->name ?? '' }} </option>
                                    @endif
                                @else
                                    <option value="all" selected> All </option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label for="filter_dom" class="block text-sm font-semibold text-slate-700">User</label>
                            <select name="filter_dom" id="filter_dom">
                                @if(!empty(request('filter_dom')) && request('filter_dom') != 'all')
                                    @if(!empty($domFilter))
                                        <option value="{{ request('filter_dom') }}"> {{ $domFilter->employee_id ?? '' }} - {{ $domFilter->name ?? '' }} {{ $domFilter->middle_name ?? '' }} {{ $domFilter->last_name ?? '' }} </option>
                                    @endif
                                @else
                                    <option value="all" selected> All </option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label for="filter_status" class="block text-sm font-semibold text-slate-700">Status</label>
                            <select name="filter_status" id="filter_status" class="form-control">
                                <option value="all">All</option>
                                @foreach ($assetStatuses as $status)
                                    <option value="{{ $status->id }}" @if(request('filter_status') == $status->id) selected @endif> {{ $status->title }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($stores as $branch)
                @php
                    $toExpireIn = !empty($branch->po_date) ? \Carbon\Carbon::parse($branch->po_date)->addMonths((int)($branch->lifespan ?? 0)) : null;
                    $expiry = $toExpireIn ? $toExpireIn->diffInDays(\Carbon\Carbon::now(), false) : null;
                @endphp

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-slate-900 font-semibold text-lg leading-tight truncate">
                                    {{ $branch->name }}
                                </div>
                                <div class="text-slate-500 text-sm mt-1">
                                    <span class="font-semibold text-slate-600">Location:</span>
                                    {{ $branch->store->name ?? '-' }}
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 shrink-0">
                                @if(isset($branch->assetStatus))
                                    <span class="als-status-badge inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white"
                                        data-bg="{{ $branch->assetStatus->color }}">
                                        {{ $branch->assetStatus->title }}
                                    </span>
                                @endif

                                @if($expiry !== null)
                                    @if($expiry >= 1 && $expiry <= 60)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            Near Expiry ({{ $toExpireIn ? $toExpireIn->format('d-m-Y') : '' }})
                                        </span>
                                    @elseif($expiry <= 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">
                                            <i class="bi bi-x-circle-fill"></i>
                                            Expired
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-semibold text-slate-500">Unique Code</div>
                                <div class="text-sm font-semibold text-slate-900 truncate">{{ $branch->ucode }}</div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-semibold text-slate-500">Code</div>
                                <div class="text-sm font-semibold text-slate-900 truncate">{{ $branch->code }}</div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-semibold text-slate-500">User</div>
                                <div class="text-sm text-slate-900 truncate">
                                    {{ isset($branch->dom) ? ($branch->dom->employee_id . ' - ' . $branch->dom->name) : '-' }}
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-semibold text-slate-500">QR Label</div>
                                <div class="text-sm">
                                    <a class="text-indigo-600 hover:text-indigo-800 font-semibold" target="_blank"
                                        href="{{ $branch->scan_links['qr_code_label'] ?? '' }}">
                                        View
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-semibold text-slate-500">Barcode Label</div>
                                <div class="text-sm">
                                    <a class="text-indigo-600 hover:text-indigo-800 font-semibold" target="_blank"
                                        href="{{ $branch->scan_links['barcode_label'] ?? '' }}">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 pb-4 sm:px-5 sm:pb-5">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('assets.edit', $branch->id) }}"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                                <i class="bi bi-pencil-square"></i>
                                Edit
                            </a>

                            {!! Form::open([
                                'method' => 'DELETE',
                                'route' => ['assets.destroy', $branch->id],
                                'style' => 'display:inline; width: 50%;',
                            ]) !!}
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 deleteGroup">
                                    <i class="bi bi-trash"></i>
                                    Delete
                                </button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3">
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="mt-3 text-slate-900 font-semibold">No assets found</div>
                        <div class="mt-1 text-slate-500 text-sm">Try adjusting filters or add a new asset.</div>
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

        // Apply dynamic status colors (avoid Blade-in-style lint issues)
        $('[data-als] .als-status-badge').each(function () {
            const bg = $(this).data('bg');
            if (bg) {
                this.style.backgroundColor = bg;
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
        }).on('change', function() {

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
    });
</script>
@endpush