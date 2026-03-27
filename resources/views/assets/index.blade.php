@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />

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

<div class="row">
    <form action="{{ route('assets.index') }}" method="GET" class="col-lg-12">
        <div class="row">
            <div class="col-2 col-xl-2 col-lg-4">
                <label for="filter_name" class="form-label"> Name </label>
                <input type="text" class="form-control" name="filter_name" id="filter_name" placeholder="Name" @if(!empty(request('filter_name'))) value="{{ request('filter_name') }}" @endif>
            </div>
            <div class="col-2 col-xl-2 col-lg-4">
                <label for="filter_code" class="form-label"> Code </label>
                <input type="text" class="form-control" name="filter_code" id="filter_code" placeholder="Code" @if(!empty(request('filter_code'))) value="{{ request('filter_code') }}" @endif>
            </div>
            <div class="col-2 col-xl-2 col-lg-4">
                <label for="filter_location" class="form-label"> Unique Code </label>
                <input type="text" class="form-control" name="filter_ucode" id="filter_ucode" placeholder="Unique Code" @if(!empty(request('filter_ucode'))) value="{{ request('filter_ucode') }}" @endif>
            </div>

            <div class="col-2 col-xl-2 col-lg-4">
                <label for="filter_location" class="form-label"> Location </label>
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

            <div class="col-2 col-xl-2 col-lg-4">
                <label for="filter_dom" class="form-label"> Location </label>
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

            <div class="col-2 col-xl-2 col-lg-4">
                <label for="filter_status" class="form-label"> Status </label>
                <select name="filter_status" id="filter_status" class="form-control">
                    <option value="all">All</option>
                    @foreach ($assetStatuses as $status)
                    <option value="{{ $status->id }}" @if(request('filter_status') == $status->id) selected @endif> {{ $status->title }} </option>
                    @endforeach
                </select>
            </div>

        </div>
        <br>
        <div class="row export-button">
            <div class="col-6 col-xl-6 col-lg-6">
                <button type="submit" class="btn btn-success me-2"> Search </button>
                <a href="{{ route('assets.index') }}" class="btn btn-danger  @if(empty($locationFilter) && empty($domFilter) && empty(request('filter_code')) && empty(request('filter_ucode')) && empty(request('filter_name')) && empty(request('filter_status'))) d-none @endif"> Clear </a>
            </div>
            <div class="col-6 col-xl-6 col-lg-4">
                <a href="{{ route('assets.create') }}" class="btn btn-primary float-end ms-2" > Add Asset </a>
                {{-- <button type="button" class="btn btn-success float-end ms-2" id="export-stores"> Export </button>
                <button type="button" class="btn btn-success float-end" data-bs-toggle="modal" data-bs-target="#browser-file"> Import </button> --}}
            </div>
        </div>  
    </form>
    <hr>
    <div class="row">
        <div class="col-12 col-xl-12 col-lg-12">
            <div class="col-title mb-30">
                <h2>Listed Assets</h2>
            </div>
            <div class="listed-brances">
                <div class="row" id="brances_listed">

                    @forelse ($stores as $branch)
                    <div class="col-lg-12 col-xl-4">
                        <div class="listing-box">
                            <p class="title main-title">
                                {{ $branch->name }}


                                @php
                                    $toExpireIn = \Carbon\Carbon::parse($branch->po_date)->addMonths($branch->lifespan);
                                    $expiry = $toExpireIn->diffInDays(\Carbon\Carbon::now());
                                @endphp

                                @if($expiry >= 1 && $expiry <= 60)
                                    &<span class="badge bg-danger float-end" style="margin-left:10px;">
                                        Near Expiry ({{ $toExpireIn->format('d-m-Y') }})
                                    </span>
                                @elseif($expiry <= 0)
                                    <span class="badge bg-danger float-end" style="margin-left:10px;">
                                        Expired
                                    </span>
                                @endif

                                @if(isset($branch->assetStatus))
                                    <span class="badge float-end" style="background-color: {{ $branch->assetStatus->color }}; color: #fff;">
                                        {{ $branch->assetStatus->title }}
                                    </span>
                                @endif
                            </p>

                            <div class="wrapper">
                                <p class="title">Unique Code:</p>
                                <p class="description">{{ $branch->ucode }}</p>
                            </div>
                            <div class="wrapper">
                                <p class="title">Code:</p>
                                <p class="description">{{ $branch->code }}</p>
                            </div>
                            <div class="wrapper">
                                <p class="title">User:</p>
                                <p class="description">{{ isset($branch->dom) ? ($branch->dom->employee_id . ' - ' . $branch->dom->name) : '' }}</p>
                            </div>

                            <div class="wrapper btn-wrapper">

                                <a style="margin-right:20px;" href="{{ route('assets.edit', $branch->id) }}"><button type="button" class="btn btn-warning w-100"> Edit </button></a>

                                {!! Form::open([
                                'method' => 'DELETE',
                                'route' => ['assets.destroy', $branch->id],
                                'style' => 'display:inline',
                                ]) !!}

                                <button style="margin-right:20px;" type="submit" class="btn btn-danger w-100 deleteGroup">Delete</button>

                                {!! Form::close() !!}

                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse

                    <div class="d-flex justify-content-center mt-4">
                        {{ $stores->links() }}
                    </div>


                </div>
                <!-- Modal -->
                <div class="modal fade" id="locationURLMap" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="locationURLMapLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width:1700px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel"> Map </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row" id="location_url_map">
                                    <div class="col-12">
                                        <input id="pac-input" class="controls" type="text"
                                            placeholder="Search Box" />
                                        <div id="map"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="saveLocation">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>

<script>
    $(document).ready(function () {
       
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