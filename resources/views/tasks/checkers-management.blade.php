@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }}
            <button type="button" class="float-end btn btn-sm btn-success mt-3" id="add-checker-level"><i class="fas fa-plus"></i> Add Checker Level</button>    
        </h1>
        <div class="lead">
            {{ $page_description }}
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="row mt-4">
            <div id="checkers-container">
            </div>
        </div>

        <button type="button" id="sbmtBtn" class="btn btn-sm btn-success mt-3"> Save </button>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            let checkerLevelCount = 0;

            function addCheckerRow(userId = null, userName = '') {
                checkerLevelCount++;
                let html = `
                        <div class="row align-items-end mb-2 checker-row" data-level="${checkerLevelCount}">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Level <span class="level-text">${checkerLevelCount}</span></label>
                            </div>
                            <div class="col-md-7">
                                <select class="form-select checker-user-select" required>
                                    ${userId ? `<option value="${userId}" selected>${userName}</option>` : ''}
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-checker-row"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    `;
                $('#checkers-container').append(html);
                initCheckerSelect2($('.checker-user-select').last());
                reindexCheckers();
            }

            function reindexCheckers() {
                let count = 1;
                $('.checker-row').each(function () {
                    $(this).attr('data-level', count);
                    $(this).find('.level-text').text(count);
                    count++;
                });
                checkerLevelCount = count - 1;
            }

            $('#add-checker-level').on('click', function () {
                addCheckerRow();
            });

            $(document).on('click', '.remove-checker-row', function () {
                let that = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to remove this checker level?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, continue!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(that).closest('.checker-row').remove();
                        reindexCheckers();
                    }
                });
            });

            function initCheckerSelect2(element) {
                $(element).select2({
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
                                ignoreDesignation: 1
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
                        return data.loading ? data.text : $('<span></span>').text(data.text);
                    }
                }).on('select2:select', function (e) {
                    let currentVal = $(this).val();
                    let currentSelect = $(this);
                    let isDuplicate = false;

                    $('.checker-user-select').not(currentSelect).each(function () {
                        if ($(this).val() === currentVal && currentVal !== null) {
                            isDuplicate = true;
                        }
                    });

                    if (isDuplicate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate User',
                            text: 'This user is already assigned to a different level.',
                        });
                        $(this).val(null).trigger('change');
                    }
                });
            }

            @if(isset($task) && $task->ckrs && $task->ckrs->count() > 0)
                @foreach($task->ckrs as $checker)
                    addCheckerRow("{{ $checker->user_id }}", "{{ isset($checker->user->id) ? ($checker->user->employee_id . ' - ' . $checker->user->name . ' ' . $checker->user->middle_name . ' ' . $checker->user->last_name) : '' }}");
                @endforeach
            @endif

            $('#sbmtBtn').click(function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to save these checkers for this task?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, continue!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let checkersData = [];
                        $('.checker-user-select').each(function () {
                            if ($(this).val()) {
                                checkersData.push($(this).val());
                            }
                        });

                        $.ajax({
                            url: "{{ route('task-checkers', $id) }}",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'PUT',
                                checkers: checkersData
                            },
                            beforeSend: function () {
                                $('body').find('.LoaderSec').removeClass('d-none');
                            },
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire('Success', response.message, 'success');
                                    location.reload();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function (response) {
                                if ('responseJSON' in response && 'errors' in response.responseJSON) {
                                    if ('name' in response.responseJSON.errors) {
                                        if (response.responseJSON.errors.name.length > 0) {
                                            Swal.fire('Error', response.responseJSON.errors.name[0], 'error');
                                        }
                                    }
                                }
                            },
                            complete: function (response) {
                                $('body').find('.LoaderSec').addClass('d-none');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush