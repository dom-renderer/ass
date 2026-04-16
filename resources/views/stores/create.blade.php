@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
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
    </style>
@endpush

@section('content')

    <div class="row">
        <div class="col-lg-12 mb-3">
            <a href="{{ route('stores.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to
                Locations</a>
        </div>

        <div class="col-lg-8 col-xl-6">

            <div class="col-title mb-30">
                <h2>Add Location</h2>
            </div>

            <div class="fursa-form">
                <form method="POST" action="{{ route('stores.store') }}" class="gift-submit-form"> @csrf

                    <input name="location" id="location" type="hidden">

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="store_type"> Type </label>
                                <select name="store_type" id="store_type" class="form-control" required>
                                    <option value=""></option>
                                    @foreach ($storeTypes as $typeRow)
                                        <option value="{{ $typeRow->id }}"> {{ $typeRow->name }} </option>
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
                                <label class="form-label" for="model_type"> Model Type </label>
                                <select name="model_type" id="model_type" class="form-control" required>
                                    <option value=""></option>
                                    @foreach ($modelTypes as $typeRow)
                                        <option value="{{ $typeRow->id }}"> {{ $typeRow->name }} </option>
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
                                <label class="form-label" for="store_category">Category</label>
                                <select name="store_category" id="store_category" class="form-control">
                                    <option value=""></option>
                                    @foreach ($storeCategories as $category_row)
                                        <option value="{{ $category_row->id }}"> {{ $category_row->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('store_category'))
                        <span class="text-danger text-left">{{ $errors->first('store_category') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="locname"> Name </label>
                                <input name="name" type="text" class="form-control" id="locname" placeholder="Location Name"
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
                                <label class="form-label" for="loccode"> Code </label>
                                <input name="code" type="text" class="form-control" id="loccode" placeholder="Location Code"
                                    required>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('code'))
                        <span class="text-danger text-left">{{ $errors->first('code') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="uniquecode"> Unique Code </label>
                                <input name="ucode" type="text" class="form-control" id="uniquecode"
                                    placeholder="Unique Code" required>
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('ucode'))
                        <span class="text-danger text-left">{{ $errors->first('ucode') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="number_of_tables">Number of tables</label>
                                <input name="number_of_tables" id="number_of_tables" type="number" min="0" max="9999"
                                    class="form-control" value="{{ old('number_of_tables', 0) }}">
                                @if ($errors->has('number_of_tables'))
                                    <span class="text-danger text-left">{{ $errors->first('number_of_tables') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="searchTextField"> Address Line 1 </label>
                                <input name="address1" type="text" class="form-control" id="searchTextField"
                                    placeholder="Address Line 1">
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('address1'))
                        <span class="text-danger text-left">{{ $errors->first('address1') }}</span>
                    @endif

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="addrln2"> Address Line 2 </label>
                                <input name="address2" type="text" class="form-control" id="addrln2"
                                    placeholder="Address Line 2">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-6">

                                <label class="form-label" for="block"> Block </label>
                                <input name="block" type="text" class="form-control" placeholder="Block" id="block">
                                @if ($errors->has('block'))
                                    <span class="text-danger text-left">{{ $errors->first('block') }}</span>
                                @endif
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="street"> Street </label>
                                <input name="street" type="text" class="form-control" placeholder="Street">
                                @if ($errors->has('street'))
                                    <span class="text-danger text-left">{{ $errors->first('street') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="landmark"> Landmark </label>
                                <input name="landmark" type="text" id="landmark" class="form-control"
                                    placeholder="Landmark">
                                @if ($errors->has('landmark'))
                                    <span class="text-danger text-left">{{ $errors->first('landmark') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="state"> State </label>
                                <select name="state" id="state" required></select>
                                @if ($errors->has('state'))
                                    <span class="text-danger text-left">{{ $errors->first('state') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="city"> City </label>
                                <select name="city" id="city" required></select>
                                @if ($errors->has('city'))
                                    <span class="text-danger text-left">{{ $errors->first('city') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="dom_id"> DoM </label>
                                <select name="dom_id" id="dom_id" required></select>
                                @if ($errors->has('dom_id'))
                                    <span class="text-danger text-left">{{ $errors->first('dom_id') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="location_url"> Location URL </label>
                                <div class="input-group mb-3">
                                    <input name="location_url" type="text" class="form-control" id="location_url"
                                        placeholder="Location URL" onkeydown="return false;"
                                        style="caret-color: transparent !important;" />
                                    <span class="input-group-text cursor-pointer" id="map-location" data-bs-toggle="modal"
                                        data-bs-target="#locationURLMap"><i class="bi bi-pin-map"></i></span>
                                </div>

                                @if ($errors->has('location_url'))
                                    <span class="text-danger text-left">{{ $errors->first('location_url') }}</span>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="map_latitude"> Latitude </label>
                                <input name="map_latitude" type="text" class="form-control" id="map_latitude"
                                    placeholder="Map Latitude" style="caret-color: transparent !important;" />

                                @if ($errors->has('map_latitude'))
                                    <span class="text-danger text-left">{{ $errors->first('map_latitude') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">

                                <label class="form-label" for="map_longitude"> Longitude </label>
                                <input name="map_longitude" type="text" class="form-control" id="map_longitude"
                                    placeholder="Map Longitude" style="caret-color: transparent !important;" />

                                @if ($errors->has('map_longitude'))
                                    <span class="text-danger text-left">{{ $errors->first('map_longitude') }}</span>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="email"> Email </label>
                                <input name="email" type="email" class="form-control" placeholder="Email" id="email">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="mobile"> Mobile Number </label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="prefix-mobile">+91</span>
                                    <input name="mobile" type="hidden" class="form-control" id="mobile"
                                        placeholder="Mobile">
                                    <input name="mobile_type" type="text" class="form-control" id="mobile_type"
                                        placeholder="Mobile">

                                    @if ($errors->has('mobile_type'))
                                        <span class="text-danger text-left">{{ $errors->first('mobile_type') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="whatsapp"> Whatsapp Number </label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="prefix-whatsapp">+91</span>
                                    <input name="whatsapp" type="hidden" class="form-control" id="whatsapp"
                                        placeholder="WhatsApp">
                                    <input name="whatsapp_type" type="text" class="form-control" id="whatsapp_type"
                                        placeholder="WhatsApp">

                                    @if ($errors->has('whatsapp_type'))
                                        <span class="text-danger text-left">{{ $errors->first('whatsapp_type') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="optime"> Opening Time </label>
                                <input name="open_time" type="text" class="form-control timepicker" id="optime"
                                    value="{{ old('open_time') }}" placeholder="Opening Time" required>
                                @if ($errors->has('open_time'))
                                    <span class="text-danger text-left">{{ $errors->first('open_time') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="cltime"> Closing Time </label>
                                <input name="close_time" type="text" class="form-control timepicker" id="cltime"
                                    placeholder="Closing Time" value="{{ old('close_time') }}" required>
                                @if ($errors->has('close_time'))
                                    <span class="text-danger text-left">{{ $errors->first('close_time') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="optime"> Operation Start Time </label>
                                <input name="ops_start_time" type="text" class="form-control timepicker" id="optime"
                                    value="{{ old('ops_start_time') }}" placeholder="Operation Start Time" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-row">
                            <div class="col-12">
                                <label class="form-label" for="cltime"> Operation End Time </label>
                                <input name="ops_end_time" type="text" class="form-control timepicker" id="cltime"
                                    placeholder="Operation End Time" value="{{ old('ops_end_time') }}" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-fursa-form-submit"><i class="bi bi-plus-circle"></i>
                        Save</button>

                </form>
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="locationURLMap" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="locationURLMapLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:1700px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"> Map </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row" id="location_url_map">
                        <div class="col-12">
                            <input id="pac-input" class="controls" type="text" placeholder="Search Box" />
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

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
        integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places" async
        defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        (function () {
            'use strict';
            window.addEventListener('load', function () {
                var forms = document.getElementsByClassName('gift-submit-form');
                var validation = Array.prototype.filter.call(forms, function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                            $(form).trigger('mdFormValidationErrors')
                        } else {
                            $(form).trigger('mdFormValidationSuccess')
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        jQuery(document).ready(function ($) {

            // Select2 for Create Form
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
            }).on('change', function () {
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
                    data: function (params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function () {
                                return $('#state option:selected').val();
                            }
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

            $('#dom_id').select2({
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
                            roles: "{{ implode(',', [Helper::$roles['divisional-operations-manager']]) }}"
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

            $('#store_type').select2({
                placeholder: 'Select Location Type',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#model_type').select2({
                placeholder: 'Select Model Type',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#store_category').select2({
                placeholder: 'Select Location Category',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('.timepicker').timepicker({
                timeFormat: 'h:mm p',
                interval: 15,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });

            $('#mobile_type,#whatsapp').mask('0#');
            $('#mobile_type').on('input', function (e) {
                $("#mobile").val("91" + e.target.value);
                if (e.target.value.length > 0 && e.target.value.length < 8) {
                    this.setCustomValidity('Please enter a valid mobile number!');
                } else {
                    this.setCustomValidity('');
                }
            });
            $('#whatsapp_type').on('input', function (e) {
                $("#whatsapp").val("91" + e.target.value);
                if (e.target.value.length > 0 && e.target.value.length < 8) {
                    this.setCustomValidity('Please enter a valid whatsapp number!');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        function initialize() {
            var input = document.getElementById('searchTextField');
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    return;
                }
            });
        }

        let thisLat, thisLong, thisLatLongUrl, thePlaceName = null;

        function initAutocomplete() {
            const map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: -33.8688, lng: 151.2195 },
                zoom: 13,
                mapTypeId: "roadmap",
            });

            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);

            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });

            let marker = null;

            function createMarker(position, title) {
                if (marker) {
                    marker.setMap(null);
                }

                const icon = {
                    url: "{{ url('assets/images/markers.png') }}",
                    scaledSize: new google.maps.Size(30, 30),
                };

                marker = new google.maps.Marker({
                    position,
                    map,
                    icon,
                    title,
                });

                return marker;
            }


            function logPlaceDetails(position, name) {
                const lat = position.lat();
                const lng = position.lng();
                const url = `https://www.google.com/maps?q=${lat},${lng}`;
                console.log(`Latitude: ${lat}, Longitude: ${lng}, URL: ${url}`);

                thisLat = lat;
                thisLong = lng;
                thisLatLongUrl = url;
                thePlaceName = name;
            }

            map.addListener("click", (event) => {
                const position = event.latLng;
                createMarker(position, "");
                logPlaceDetails(position, "");
            });

            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                const bounds = new google.maps.LatLngBounds();

                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }

                    const position = place.geometry.location;
                    createMarker(position, place.name);
                    logPlaceDetails(position, place.name);

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(position);
                    }
                });

                map.fitBounds(bounds);
            });
        }


        const locationURLMapModal = document.getElementById('locationURLMap');
        locationURLMapModal.addEventListener('shown.bs.modal', function (event) {
            initAutocomplete();

            document.getElementById('saveLocation').addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('location').value = thePlaceName;
                document.getElementById('location_url').value = thisLatLongUrl;
                document.getElementById('map_latitude').value = thisLat;
                document.getElementById('map_longitude').value = thisLong;

                $(locationURLMapModal).modal('hide');
            }, false);
        })

    </script>
@endpush