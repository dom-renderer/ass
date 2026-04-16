@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] },
                }
            }
        }
    </script>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>

    <style type="text/css">
        body { font-family: 'DynamicAppFont', sans-serif !important; }
        .dataTables_wrapper .dataTables_length select { border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .dataTables_wrapper .dataTables_filter input { border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 0.25rem 0.75rem; font-size: 0.875rem; outline: none; }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        table.dataTable { border-collapse: collapse !important; margin-top: 0 !important; margin-bottom: 0 !important; width: 100% !important; }
        table.dataTable thead th { border-bottom: 1px solid #f3f4f6 !important; border-top: none !important; color: #6b7280; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 0.75rem 1.5rem !important; background-color: #f9fafb; }
        table.dataTable tbody td { border-bottom: 1px solid #f3f4f6 !important; padding: 1rem 1.5rem !important; vertical-align: middle; color: #374151; font-size: 0.875rem; }
        table.dataTable tbody tr:hover { background-color: #f9fafb !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 !important; border: none !important; }
    </style>
@endpush

@section('content')

    <div class="px-6 pt-6 pb-4">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">{{ $page_title ?? 'Asset Makes' }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">{{ $page_description ?? 'Manage Asset Makes in the system.' }}</p>
            </div>
            <div class="flex items-center mt-4 md:mt-0">
                @if (auth()->user()->can('assets-makes.create'))
                    <a href="{{ route('assets-makes.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                        <i class="bi bi-plus-lg"></i> Add Asset Make
                    </a>
                @endif
            </div>
        </div>
        
        <div class="mt-2 mb-4">
            @include('layouts.partials.messages')
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2 bg-gray-50/50">
                        <i class="bi bi-wrench text-gray-400 text-lg"></i>
                        <h3 class="text-sm font-semibold text-gray-800 m-0">Asset Makes Directory</h3>
                    </div>
                    <div class="p-0 table-responsive">
                        <table class="table table-striped w-100 m-0" id="role-table" cellspacing="0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th width="15%" class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function($){

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Asset Make?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        let usersTable = new DataTable('#role-table', {
            dom: '<"px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4"lf>rt<"px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4"pi><"clear">',
            pageLength: 50,
            ajax: {
                url: "{{ route('assets-makes.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {});
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'name' },
                 { data: 'action', className: 'text-center' }
            ],
            initComplete: function(settings) {
            }
        });
        
    });
</script>  
@endpush