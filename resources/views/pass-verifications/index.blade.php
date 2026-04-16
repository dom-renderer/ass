@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="row">
            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="location-filter"> Location </label>
                <select id="location-filter" multiple>
                    @if(session()->has('pass_verification_loc'))
                        @foreach (session()->get('pass_verification_loc') as $thisLocId => $thisLocName)
                            <option value="{{ $thisLocId }}" selected> {{ $thisLocName }} </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="verified-by-filter"> Verified By </label>
                <select id="verified-by-filter" multiple>
                    @if(session()->has('pass_verification_verified_by'))
                        @foreach (session()->get('pass_verification_verified_by') as $thisUserId => $thisUserName)
                            <option value="{{ $thisUserId }}" selected> {{ $thisUserName }} </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="pass-number-filter"> Entered Pass Number </label>
                <input type="text" id="pass-number-filter" class="form-control" value="{{ session()->get('pass_verification_pass_number', '') }}">
            </div>

            @php
                $hasValSession = session()->has('pass_verification_validation_type');
                $ValSession = session()->get('pass_verification_validation_type', []);
            @endphp
            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="validation-type-filter"> Validation Type </label>
                <select id="validation-type-filter" multiple>
                    <option @if($hasValSession && in_array(0, $ValSession)) selected @endif value="0"> Scanned </option>
                    <option @if($hasValSession && in_array(1, $ValSession)) selected @endif value="1"> Manual </option>
                </select>
            </div>

            @php
                $hasValidSession = session()->has('pass_verification_is_valid');
                $ValidSession = session()->get('pass_verification_is_valid', []);
            @endphp
            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="is-valid-filter"> Valid/Invalid </label>
                <select id="is-valid-filter" multiple>
                    <option @if($hasValidSession && in_array(0, $ValidSession)) selected @endif value="0"> Valid </option>
                    <option @if($hasValidSession && in_array(1, $ValidSession)) selected @endif value="1"> Invalid </option>
                </select>
            </div>

            @php
                $hasEntrySession = session()->has('pass_verification_entry_type');
                $EntrySession = session()->get('pass_verification_entry_type', []);
            @endphp
            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="entry-type-filter"> Entry Type </label>
                <select id="entry-type-filter" multiple>
                    <option @if($hasEntrySession && in_array(1, $EntrySession)) selected @endif value="1"> Entry </option>
                    <option @if($hasEntrySession && in_array(2, $EntrySession)) selected @endif value="2"> Exit </option>
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="task-date-range-picker"> Date </label>
                <input type="text" id="task-date-range-picker" class="form-control" readonly />
            </div>

            <div class="col-2 col-lg-6 col-xl-6 mt-1 col-xxl-2">
                <button id="filter-data" class="btn btn-secondary me-2" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger @if(!(session()->has('pass_verification_loc') || session()->has('pass_verification_verified_by') || session()->has('pass_verification_validation_type') || session()->has('pass_verification_from') || session()->has('pass_verification_to') || session()->has('pass_verification_is_valid') || session()->has('pass_verification_entry_type') || session()->has('pass_verification_pass_number'))) d-none @endif" style="position: relative;top:34px;"> Clear </button>
            </div>
        </div>
        
        <div class="tab-content mt-4" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>Location</th>
                            <th>Task Title</th>
                            <th>Pass Number</th>
                            <th>Validation Type</th>
                            <th>Valid Status</th>
                            <th>Entry Type</th>
                            <th>Verified By</th>
                            <th>Created At</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>
<script>
    
    $(document).ready(function($){

        @if(session()->has('pass_verification_from') && !empty(session()->get('pass_verification_from')))
            var startTaskDate = moment("{{ session()->get('pass_verification_from') }}", 'DD-MM-YYYY');
        @else
            var startTaskDate = moment().startOf('month');
        @endif

        @if(session()->has('pass_verification_to') && !empty(session()->get('pass_verification_to')))
            var endTaskDate = moment("{{ session()->get('pass_verification_to') }}", 'DD-MM-YYYY');
        @else
            var endTaskDate = moment().endOf('month');
        @endif

        function cb(start, end) {
            $('#task-date-range-picker').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
        }

        $('#task-date-range-picker').daterangepicker({
            startDate: startTaskDate,
            endDate: endTaskDate,
            locale: {
                format: 'DD-MM-YYYY'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(startTaskDate, endTaskDate);

        $('#task-date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            startTaskDate = picker.startDate;
            endTaskDate = picker.endDate;

            tasksDataTable.ajax.reload();
        });        

        $.fn.dataTable.ext.errMode = 'none';
        
        let tasksDataTable = new DataTable('#role-table', {
            "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
            ajax: {
                url: "{{ route('pass-verifications.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        loc: $('#location-filter').val(),
                        verified_by: $('#verified-by-filter').val(),
                        pass_number: $('#pass-number-filter').val(),
                        validation_type: $('#validation-type-filter').val(),
                        is_valid: $('#is-valid-filter').val(),
                        entry_type: $('#entry-type-filter').val(),
                        from : startTaskDate.format('DD-MM-YYYY'),
                        to : endTaskDate.format('DD-MM-YYYY')
                    });
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'location_name' },
                 { data: 'task_title' },
                 { data: 'entered_pass_number' },
                 { data: 'validation_type' },
                 { data: 'is_valid' },
                 { data: 'entry_type' },
                 { data: 'verified_by_name' },
                 { data: 'created_at' }
            ]
        });

        $('#verified-by-filter').select2({
            placeholder: "Select Verified By",
            allowClear: true,
            width: "100%",
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
                        _token: "{{ csrf_token() }}"
                    }
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

        $('#location-filter').select2({
            placeholder: 'Select Locations',
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
                        assetswloc: 1,
                        type: 1,
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
            }
        });

        $('#validation-type-filter, #is-valid-filter, #entry-type-filter').select2({
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $(document).on('click', '#filter-data', function () {
            tasksDataTable.ajax.reload();

            let locsFilter = $('#location-filter').val();
            let verifiedByFilter = $('#verified-by-filter').val();
            let passNumberFilter = $('#pass-number-filter').val();
            let valTypeFilter = $('#validation-type-filter').val();
            let isValidFilter = $('#is-valid-filter').val();
            let entryTypeFilter = $('#entry-type-filter').val();

            if (locsFilter || verifiedByFilter || valTypeFilter || isValidFilter || entryTypeFilter || passNumberFilter) {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            if (!$('#filter-data-clear').hasClass('d-none')) {
                $('#filter-data-clear').addClass('d-none');
            }

            $('#location-filter').val(null).trigger('change');
            $('#verified-by-filter').val(null).trigger('change');
            $('#pass-number-filter').val('');
            $('#validation-type-filter').val(null).trigger('change');
            $('#is-valid-filter').val(null).trigger('change');
            $('#entry-type-filter').val(null).trigger('change');

            tasksDataTable.ajax.reload();
        });

    });
 </script>  
@endpush
