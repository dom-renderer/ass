@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="lead d-flex flex-wrap align-items-center gap-2">
        @can('menu_promotions.create')
            <a href="{{ route('menu.promotions.create') }}" class="btn btn-primary btn-sm ms-auto">Create Promotion</a>
        @endcan
    </div>
    @include('layouts.partials.messages')
    <table class="table table-striped w-100" id="promotions-table">
        <thead><tr><th>Name</th><th>Code</th><th>Type</th><th>Scope</th><th>Status</th><th>Priority</th><th>Actions</th></tr></thead>
    </table>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function(){
    const csrf='{{ csrf_token() }}';
    let table = new DataTable('#promotions-table',{processing:true,serverSide:true,ajax:{url:'{{ route('menu.promotions.index') }}'},columns:[
        {data:'name'},{data:'code'},{data:'type'},{data:'scope',orderable:false,searchable:false},{data:'status_badge',orderable:false,searchable:false},{data:'priority'},{data:'action',orderable:false,searchable:false}
    ]});

    $(document).on('click','.btn-delete-promotion',function(){
        const id=$(this).data('id');
        Swal.fire({title:'Delete this promotion?',icon:'warning',showCancelButton:true}).then(r=>{if(!r.isConfirmed)return;
            $.post('{{ url('menu/promotions') }}/'+id,{_method:'DELETE',_token:csrf},()=>table.ajax.reload(null,false));
        });
    });
    $(document).on('click','.btn-restore-promotion',function(){
        const id=$(this).data('id');
        $.post('{{ url('menu/promotions') }}/'+id+'/restore',{_token:csrf},()=>table.ajax.reload(null,false));
    });
});
</script>
@endpush
