@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
<style>

</style>
@endpush

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $assignment->title }}</h2>
            <p class="text-muted">Workflow Table Visualization</p>
        </div>
        <div>
            <a href="{{ route('workflow-assignments.show', encrypt($assignment->id)) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Details
            </a>
            <a href="{{ route("workflow-assignments.tree", encrypt($assignment->id)) }}" class="btn btn-success">
                <i class="bi bi-diagram-3 me-2"></i>Tree View
            </a>
        </div>
    </div>

    

    <div id="chart-container">
        <div class="bg-light p-4 rounded">
            <div class="row">
                <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                    <label class="col-form-label" for="user-filter"> Employee (Maker) </label>
                    <select id="user-filter" multiple>
                    </select>
                </div>

                <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                    <label class="col-form-label" for="checklist-locations"> Department </label>
                    <select id="checklist-locations" multiple>
                        @foreach ($allDepartments as $departmentCode => $departmentName)
                            <option value="{{ $departmentCode }}"> {{ $departmentName }} </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                    <label class="col-form-label" for="task-date-range-picker"> Date </label>
                    <input type="text" id="task-date-range-picker" class="form-control" readonly />
                </div>

                <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                    <label class="col-form-label" for="status-filter"> Status </label>
                    <select id="status-filter">
                        <option value=""></option>
                        <option value="0"> Pending </option>
                        <option value="1"> In-Progress </option>
                        <option value="2"> Pending Verification </option>
                        <option value="3"> Reassigned </option>
                        <option value="4"> Verifying </option>
                        <option value="5"> Verified </option>
                        <option value="6"> Completed </option>
                    </select>
                </div>
                
                <div class="col-2 col-lg-6 col-xl-6 mt-1 col-xxl-2">
                    <button id="filter-data" class="btn btn-secondary me-2" style="position: relative;top:34px;"> Search </button>
                    <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button id="delete-selected" class="btn btn-danger btn-sm float-end" style="display: none;">Delete</button>
                </div>
            </div>
            
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                    <div class="table-responsive">
                        <table id="workflow-assignment-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>Task</td>
                                    <td>Task Percentage</td>
                                    <td>Maker</td>
                                    <td>Date</td>
                                    <td>Task Status</td>
                                    <td>Department</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div> 
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
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
            endTaskDate = picker.endDate
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
                        ignoreDesignation: 1,
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']]) }}"
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

        $('#checklist-locations').select2({
            placeholder: 'Select Locations',
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

        $(document).on('change', '.change-status', function () {
            let orderId = $(this).data('id');
            let status = $(this).val();
            let that = $(this);

            if (!confirm('Are you sure you want to change the status?')) {
                $(this).val($(this).data('last-selected'));
                return false;
            }

            $.ajax({
                url: "{{ route('task-status-change') }}",
                type: 'GET',
                data: {
                    id: orderId,
                    status: status
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire('success', 'Status updated successfully.', 'success');
                        tasksDataTable.ajax.reload();
                    } else {
                        $(that).val($(this).data('last-selected'));                            
                    }
                },
                complete: function (response) {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
        });

        $.fn.dataTable.ext.errMode = 'none';

        let tasksDataTable = new DataTable('#workflow-assignment-table', {
            "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
            pageLength: 50,
            ajax: {
                url: "{{ route("workflow-assignments.table", encrypt($assignment->id)) }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        locs: $('#checklist-locations').val(),
                        user: $('#user-filter').val(),
                        from : startTaskDate.format('DD-MM-YYYY'),
                        to : endTaskDate.format('DD-MM-YYYY'),
                        status: $('#status-filter').val()
                    });
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            },
            searching: false,
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'DT_RowIndex', searchable: false },
                 { data: 'task_name' },
                 { data: 'task_percentage' },
                 { data: 'user_name' },
                 { data: 'task_date' },
                 { data: 'task_status' },
                 { data: 'department_name' },
                 { data: 'action' }
            ],
            initComplete: function(settings) {

            }
        });

        $(document).on('click', '#filter-data', function () {
            tasksDataTable.ajax.reload();

            let locsFilter = $('#checklist-locations').val();
            let userFilter = $('#user-filter').val();
            let status = $('#status-filter').val();

            if (anyIsset(userFilter, status, locsFilter)) {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            if (!$('#filter-data-clear').hasClass('d-none')) {
                $('#filter-data-clear').addClass('d-none');
            }

            $('#checklist-locations').val(null).trigger('change');
            $('#user-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');

            tasksDataTable.ajax.reload();
        });

    });
</script>
@endpush
