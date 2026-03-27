@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
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
                <label class="col-form-label" for="inspection-type-filter"> Type of Inspection </label>
                <select id="inspection-type-filter" class="form-control">
                    <option value=""></option>
                    <option value="0">DoM Checklist</option>
                    <option value="1">Store Checklist</option>
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="store-filter"> Store </label>
                <select id="store-filter" multiple></select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="user-filter"> User </label>
                <select id="user-filter" multiple></select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="checklist-filter"> Checklist </label>
                <select id="checklist-filter" multiple></select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="task-date-range-picker"> Date Range </label>
                <input type="text" id="task-date-range-picker" class="form-control" readonly />
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="status-filter"> Status </label>
                <select id="status-filter" class="form-control">
                    <option value=""></option>
                    <option value="1">In-Progress</option>
                    <option value="2">Pending-Verification</option>
                    <option value="3">Verified</option>
                </select>
            </div>

            <div class="col-12">
                <button id="filter-data" class="btn btn-secondary me-2" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>

                <div class="float-end mt-2">
                    <div class="fw-bold">Average TAT</div>
                    <div class="fs-4" id="avg-tat-value">0</div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped" id="tat-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Store</th>
                            <th>User</th>
                            <th>Checklist</th>
                            <th>Scheduled Date</th>
                            <th>Started At</th>
                            <th>Completed At</th>
                            <th>Status</th>
                            <th>TAT</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
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
    $(document).ready(function() {
        var startTaskDate = moment().startOf('month');
        var endTaskDate = moment().endOf('month');

        function cb(start, end) {
            $('#task-date-range-picker').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
        }

        $('#task-date-range-picker').daterangepicker({
            startDate: startTaskDate,
            endDate: endTaskDate,
            locale: { format: 'DD-MM-YYYY' },
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
        });

        $('#inspection-type-filter').select2({
            placeholder: 'Select type',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $('#status-filter').select2({
            placeholder: 'Select status',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $('#store-filter').select2({
            placeholder: 'Select Store',
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
                        _token: "{{ csrf_token() }}"
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        $('#user-filter').select2({
            placeholder: "Select a User",
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
                        _token: "{{ csrf_token() }}",
                        ignoreDesignation: 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        $('#checklist-filter').select2({
            placeholder: 'Select Checklist',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('checklists-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        type: 1,
                        _token: "{{ csrf_token() }}"
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        $.fn.dataTable.ext.errMode = 'none';

        let tatDataTable = new DataTable('#tat-table', {
            "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
            ajax: {
                url: "{{ route('tat-overview.index') }}",
                data: function (d) {
                    return $.extend({}, d, {
                        inspection_type: $('#inspection-type-filter').val(),
                        store: $('#store-filter').val(),
                        user: $('#user-filter').val(),
                        checklist: $('#checklist-filter').val(),
                        from: startTaskDate.format('DD-MM-YYYY'),
                        to: endTaskDate.format('DD-MM-YYYY'),
                        status: $('#status-filter').val()
                    });
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                complete: function (xhr) {
                    $('body').find('.LoaderSec').addClass('d-none');
                    try {
                        const json = xhr.responseJSON || {};
                        if (json.avg_tat_label !== undefined) {
                            $('#avg-tat-value').text(json.avg_tat_label);
                        } else {
                            $('#avg-tat-value').text('0');
                        }
                    } catch (e) {
                        $('#avg-tat-value').text('0');
                    }
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                { data: 'inspection_type' },
                { data: 'store' },
                { data: 'user' },
                { data: 'checklist' },
                { data: 'date' },
                { data: 'started_at' },
                { data: 'completion_date' },
                { data: 'status_label' },
                { data: 'tat' },
                { data: 'action' }
            ],
            createdRow: function(row, data, dataIndex) {}
        });

        function anyIsset() {
            for (let i = 0; i < arguments.length; i++) {
                if (arguments[i] !== undefined && arguments[i] !== null && arguments[i] !== '' && !(Array.isArray(arguments[i]) && arguments[i].length === 0)) {
                    return true;
                }
            }
            return false;
        }

        $(document).on('click', '#filter-data', function () {
            tatDataTable.ajax.reload();

            let inspectionType = $('#inspection-type-filter').val();
            let store = $('#store-filter').val();
            let user = $('#user-filter').val();
            let checklist = $('#checklist-filter').val();
            let status = $('#status-filter').val();

            if (anyIsset(inspectionType, store, user, checklist, status)) {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            $('#filter-data-clear').addClass('d-none');

            $('#inspection-type-filter').val(null).trigger('change');
            $('#store-filter').val(null).trigger('change');
            $('#user-filter').val(null).trigger('change');
            $('#checklist-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');

            startTaskDate = moment().startOf('month');
            endTaskDate = moment().endOf('month');
            cb(startTaskDate, endTaskDate);

            tatDataTable.ajax.reload();
        });
    });
</script>
@endpush

