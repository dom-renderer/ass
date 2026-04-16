@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="lead d-flex flex-wrap align-items-center gap-2">
        @can('menu_categories.create')
            <a href="{{ route('menu.categories.create') }}" class="btn btn-primary btn-sm ms-auto">Add Category</a>
        @endcan
    </div>
    @include('layouts.partials.messages')
    <table class="table table-striped w-100" id="categories-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Ordering</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function () {
    const csrf = '{{ csrf_token() }}';
    let table = new DataTable('#categories-table', {
        dom: '<"d-flex justify-content-between mb-2"f>rt<"d-flex flex-column float-start mt-3"pi>',
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('menu.categories.index') }}'
        },
        columns: [
            { data: 'image_thumb', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'slug' },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'ordering' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    $(document).on('click', '.btn-delete-category', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete this category?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('menu/categories') }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: csrf },
                success: function () {
                    Swal.fire('Deleted', '', 'success');
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Request failed', 'error');
                }
            });
        });
    });

    $(document).on('click', '.btn-restore-category', function () {
        const id = $(this).data('id');
        $.post('{{ url('menu/categories') }}/' + id + '/restore', { _token: csrf }, function () {
            Swal.fire('Restored', '', 'success');
            table.ajax.reload(null, false);
        });
    });
});
</script>
@endpush
