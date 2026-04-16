@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="lead d-flex flex-wrap align-items-center gap-2">
        @can('menu_products.create')
            <a href="{{ route('menu.products.create') }}" class="btn btn-primary btn-sm ms-auto">Add Product</a>
        @endcan
    </div>
    @include('layouts.partials.messages')
    <table class="table table-striped w-100" id="prod-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Base price</th>
                <th>Status</th>
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
    let table = new DataTable('#prod-table', {
        dom: '<"d-flex justify-content-between mb-2"f>rtpi',
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('menu.products.index') }}'
        },
        columns: [
            { data: 'image_thumb', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'category_name', orderable: false, searchable: false },
            { data: 'base_price' },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false }
        ]
    });
    $(document).on('click', '.btn-delete-product', function () {
        const id = $(this).data('id');
        Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true }).then((r) => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: '{{ url('menu/products') }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: csrf },
                success: function () { Swal.fire('Deleted', '', 'success'); table.ajax.reload(null, false); }
            });
        });
    });
    $(document).on('click', '.btn-restore-product', function () {
        const id = $(this).data('id');
        $.post('{{ url('menu/products') }}/' + id + '/restore', { _token: csrf }, function () {
            Swal.fire('Restored', '', 'success'); table.ajax.reload(null, false);
        });
    });
});
</script>
@endpush
