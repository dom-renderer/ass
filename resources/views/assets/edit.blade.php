@extends('layouts.app-master')

@push('css')
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
    </style>
@endpush

@section('content')
<form method="POST" action="{{ route('assets.update', $store->id) }}" class="gift-submit-form" enctype="multipart/form-data"> @csrf
    <div class="row">
            @method('PATCH')
            <div class="col-12">

                <div class="col-title mb-30">
                    <h2>Edit Asset</h2>
                </div>

                <div class="fursa-form row">
                    <div class="col-6">

                    <input name="location" id="location" type="hidden" value="{{ $store->location }}">

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="model_type"> Make <span class="text-danger">*</span> </label>
                                <select name="model_type" id="model_type" class="form-control" required>
                                    <option value=""></option>
                                    @foreach ($modelTypes as $typeRow)
                                    <option value="{{ $typeRow->id }}" @if($typeRow->id == $store->model_type) selected @endif> {{ $typeRow->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('model_type'))
                    <span class="text-danger text-left">{{ $errors->first('model_type') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="store_type"> Model <span class="text-danger">*</span> </label>
                                <select name="store_type" id="store_type" class="form-control" required>
                                    <option value=""></option>
                                    @foreach ($storeTypes as $typeRow)
                                    <option value="{{ $typeRow->id }}" @if($typeRow->id == $store->store_type) selected @endif> {{ $typeRow->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('store_type'))
                    <span class="text-danger text-left">{{ $errors->first('store_type') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="store_category">Category <span class="text-danger">*</span> </label>
                                <select name="store_category" id="store_category" class="form-control" required>
                                    <option value=""></option>
                                    @foreach ( $storeCategories as $category_row )
                                    <option value="{{ $category_row->id }}" @if($category_row->id == $store->store_category) selected @endif> {{ $category_row->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ( $errors->has( 'store_category' ) )
                    <span class="text-danger text-left">{{ $errors->first( 'store_category' ) }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="location_item">Location </label>
                                <select name="location_item" id="location_item" class="form-control">
                                    @foreach ( $locationItems as $locationItem )
                                        <option value="{{ $locationItem->id }}" selected>{{ $locationItem->code }} - {{ $locationItem->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ( $errors->has( 'location_item' ) )
                    <span class="text-danger text-left">{{ $errors->first( 'location_item' ) }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="dom_id"> Assign To  </label>
                                <select name="dom_id" id="dom_id">
                                    @foreach ( $assignTo as $assign )
                                        <option value="{{ $assign->id }}" selected>{{ $assign->employee_id }} - {{ $assign->name }} {{ $assign->middle_name }} {{ $assign->last_name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('dom_id'))
                                <span class="text-danger text-left">{{ $errors->first('dom_id') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ( $errors->has( 'dom_id' ) )
                    <span class="text-danger text-left">{{ $errors->first( 'dom_id' ) }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="locname"> Name </label>
                                <input name="name" type="text" class="form-control" value="{{ old('name', $store->name) }}" placeholder="Location Name" required></div>
                        </div>
                    </div>

                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="loccode"> Unique Code <span class="text-danger">*</span> </label>
                                <input name="ucode" type="text" class="form-control" id="ucode" placeholder="Unique Code" value="{{ old('ucode', $store->ucode) }}" required>
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
                            <input name="code" type="text" class="form-control" value="{{ old('code', $store->code) }}" placeholder="Location Code"></div>
                        </div>
                    </div>

                    @if ($errors->has('code'))
                        <span class="text-danger text-left">{{ $errors->first('code') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="po_date"> PO Date <span class="text-danger">*</span> </label>
                                <input name="po_date" type="date" class="form-control" id="po_date" placeholder="PO Date" value="{{ date('Y-m-d', strtotime(old('po_date', $store->po_date))) }}" required>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('po_date'))
                    <span class="text-danger text-left">{{ $errors->first('po_date') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="warranty"> Warranty (Months) <span class="text-danger">*</span> </label>
                                <input name="warranty" type="number" min="0" step="1" max="1000" class="form-control" id="warranty" placeholder="Warranty (Months)" value="{{ old('warranty', $store->warranty) }}" required>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('warranty'))
                    <span class="text-danger text-left">{{ $errors->first('warranty') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="warranty"> Lifespan (Months) <span class="text-danger">*</span> </label>
                                <input name="lifespan" type="number" min="0" step="1" max="1000" class="form-control" id="warranty" placeholder="Lifespan (Months)" value="{{ old('lifespan', $store->lifespan) }}" required>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('lifespan'))
                    <span class="text-danger text-left">{{ $errors->first('lifespan') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="asset_status_id"> Asset Status <span class="text-danger">*</span> </label>
                                <select name="asset_status_id" id="asset_status_id" class="form-control" required>
                                    <option value=""></option>
                                    @foreach ($assetStatuses as $status)
                                    <option value="{{ $status->id }}" @if($status->id == $store->asset_status_id) selected @endif style="color: {{ $status->color }}; font-weight: bold;"> {{ $status->title }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('asset_status_id'))
                    <span class="text-danger text-left">{{ $errors->first('asset_status_id') }}</span>
                    @endif

                    <button type="submit" class="btn btn-primary btn-fursa-form-submit"><i class="bi bi-plus-circle"></i> Update</button>

                </div>
                <div class="col-6">

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="primary_image"> Primary Image </label>
                                    <input type="file" class="filepond" name="primary_image" id="primary_image" data-max-file-size="3MB">
                                    @if($store->primary_image)
                                        <div class="mt-2">
                                            <p>Current Image:</p>
                                            <img src="{{ asset('storage/assets-images/' . $store->primary_image) }}" alt="Primary Image" style="max-width: 200px; max-height: 200px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="secondary_images"> Secondary Images </label>
                                    <input type="file" class="filepond" name="secondary_images[]" id="secondary_images" multiple data-max-file-size="3MB">
                                    
                                    @if($store->secondary_images)
                                        <div class="mt-2">
                                            <p>Current Secondary Images:</p>
                                            <div class="row">
                                                @foreach($store->secondary_images as $image)
                                                    <div class="col-md-3 mb-2 text-center">
                                                        <img src="{{ asset('storage/assets-images/' . $image) }}" alt="Secondary Image" class="img-fluid img-thumbnail" style="height: 100px; object-fit: cover;">
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input" type="checkbox" name="remove_secondary_images[]" value="{{ $image }}" id="remove_{{ $loop->index }}">
                                                            <label class="form-check-label text-danger" for="remove_{{ $loop->index }}">
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
                                    <input type="file" class="filepond" name="documents[]" id="documents" multiple data-max-file-size="10MB">
                                    <small class="text-muted">Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB each)</small>
                                    
                                    @if($store->documents)
                                        <div class="mt-3">
                                            <p><strong>Current Documents:</strong></p>
                                            <ul class="list-group">
                                                @foreach($store->documents as $index => $document)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <a href="{{ asset('storage/asset-documents/' . $document) }}" target="_blank">
                                                            <i class="bi bi-file-earmark"></i> {{ $document }}
                                                        </a>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="remove_documents[]" value="{{ $document }}" id="remove_doc_{{ $index }}">
                                                            <label class="form-check-label text-danger" for="remove_doc_{{ $index }}">Remove</label>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-title mb-30">
                            <h2>Assets</h2>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="assets-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">Asset</th>
                                        <th style="width: 40%">Description</th>
                                        <th style="width: 20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($locationAssets as $index => $locationAsset)
                                        @if(isset($locationAsset->asset->id))
                                        <tr id="asset-row-{{ $index }}">
                                            <td>
                                                <select name="assets[{{ $index }}][asset_id]" class="form-control asset-select" required>
                                                    <option value="{{ $locationAsset->asset_id }}" selected>{{ $locationAsset->asset->code }} - {{ $locationAsset->asset->name }}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="assets[{{ $index }}][description]" class="form-control" placeholder="Description (Optional)" value="{{ $locationAsset->description }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-asset-btn" data-row-id="{{ $index }}"><i class="bi bi-trash"></i> Remove</button>
                                            </td>
                                        </tr>
                                        @endif                                        
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="add-asset-btn"><i class="bi bi-plus"></i> Add Asset</button>
                        </div>
                    </div>
            </div>
            </div>
        </div>
    </form>


@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
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
        }).on('change', function() {
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
                    <td>
                        <select name="assets[${assetRowIndex}][asset_id]" class="form-control asset-select" required></select>
                    </td>
                    <td>
                        <input type="text" name="assets[${assetRowIndex}][description]" class="form-control" placeholder="Description (Optional)">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-asset-btn" data-row-id="${assetRowIndex}"><i class="bi bi-trash"></i> Remove</button>
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