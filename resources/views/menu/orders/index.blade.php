@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
<style>
    .ord-card { border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
    .ord-label { font-size: .72rem; color: #6b7280; margin-bottom: 4px; display: block; }
    .ord-input { height: 36px !important; font-size: .82rem !important; }
</style>
@endpush

@section('content')
<div class="bg-slate-50 p-4 rounded-xl border">
    <div class="flex justify-between items-center mb-3">
        <div>
            <h1 class="text-2xl font-semibold mb-0">{{ $page_title }}</h1>
            <p class="text-xs text-slate-500 mt-1">Track and manage order lifecycle</p>
        </div>
        @can('menu_orders.create')
            <a href="{{ route('menu.orders.create') }}" class="btn btn-primary btn-sm">Create Order</a>
        @endcan
    </div>
    @include('layouts.partials.messages')

    <div class="ord-card p-3 mb-3">
        <div class="grid md:grid-cols-5 gap-3">
            <div>
                <label class="ord-label">Store</label>
                <select id="f_store_id" class="form-control ord-input">
                    <option value="">All</option>
                    @foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="ord-label">Status</label>
                <select id="f_status" class="form-control ord-input">
                    <option value="">All</option>
                    @foreach($statuses as $st)<option value="{{ $st }}">{{ ucfirst($st) }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="ord-label">Payment</label>
                <select id="f_payment_method" class="form-control ord-input">
                    <option value="">All</option>
                    @foreach($paymentMethods as $pm)<option value="{{ $pm }}">{{ strtoupper($pm) }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="ord-label">Order #</label>
                <input id="f_order_number" class="form-control ord-input" placeholder="Search order no">
            </div>
            <div>
                <label class="ord-label">Customer Phone</label>
                <input id="f_customer_phone" class="form-control ord-input" placeholder="Search phone">
            </div>

            <div>
                <label class="ord-label">Table #</label>
                <input id="f_table_number" type="number" min="1" class="form-control ord-input" placeholder="Table no">
            </div>
            <div>
                <label class="ord-label">Min Total</label>
                <input id="f_min_total" type="number" step="0.01" min="0" class="form-control ord-input">
            </div>
            <div>
                <label class="ord-label">Max Total</label>
                <input id="f_max_total" type="number" step="0.01" min="0" class="form-control ord-input">
            </div>
            <div>
                <label class="ord-label">Date From</label>
                <input id="f_date_from" type="date" class="form-control ord-input">
            </div>
            <div>
                <label class="ord-label">Date To</label>
                <input id="f_date_to" type="date" class="form-control ord-input">
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" id="applyFilters">Apply Filters</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilters">Clear</button>
        </div>
    </div>

    <div class="ord-card p-3">
        <table class="table table-striped w-100" id="orders-table">
            <thead>
                <tr>
                    <th>Order #</th><th>Store</th><th>Table</th><th>Customer</th><th>Status</th><th>Change Status</th><th>Total</th><th>Created</th><th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function(){
    const csrf='{{ csrf_token() }}';
    const table = new DataTable('#orders-table',{processing:true,serverSide:true,ajax:{
        url:"{{ route('menu.orders.index') }}",
        data: function(d){
            d.store_id = $('#f_store_id').val();
            d.status = $('#f_status').val();
            d.payment_method = $('#f_payment_method').val();
            d.order_number = $('#f_order_number').val();
            d.customer_phone = $('#f_customer_phone').val();
            d.table_number = $('#f_table_number').val();
            d.min_total = $('#f_min_total').val();
            d.max_total = $('#f_max_total').val();
            d.date_from = $('#f_date_from').val();
            d.date_to = $('#f_date_to').val();
        }
    },columns:[
        {data:'order_number'},
        {data:'store_name'},
        {data:'table_number'},
        {data:'customer_phone'},
        {data:'status_badge',orderable:false,searchable:false},
        {data:'status_control',orderable:false,searchable:false},
        {data:'grand_total'},
        {data:'created_at'},
        {data:'action',orderable:false,searchable:false}
    ]});

    $('#applyFilters').on('click', function(){ table.ajax.reload(); });
    $('#clearFilters').on('click', function(){
        $('#f_store_id,#f_status,#f_payment_method').val('');
        $('#f_order_number,#f_customer_phone,#f_table_number,#f_min_total,#f_max_total,#f_date_from,#f_date_to').val('');
        table.ajax.reload();
    });

    $(document).on('click','.btn-delete-order',function(){
        const id=$(this).data('id');
        Swal.fire({title:'Delete this order?',icon:'warning',showCancelButton:true}).then(r=>{ if(!r.isConfirmed) return;
            $.post("{{ url('menu/orders') }}/"+id,{_token:csrf,_method:'DELETE'},function(){ table.ajax.reload(null,false); });
        });
    });

    $(document).on('focus', '.order-status-select', function(){
        $(this).data('previous', $(this).val());
    });

    $(document).on('change','.order-status-select',function(){
        const id=$(this).data('id');
        const next=$(this).val();
        const prev=$(this).data('previous') || '';
        const select=$(this);
        Swal.fire({
            title: 'Change order status?',
            text: 'Set status to ' + next.charAt(0).toUpperCase() + next.slice(1) + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update'
        }).then(r=>{
            if(!r.isConfirmed){
                select.val(prev);
                return;
            }
            $.post("{{ url('menu/orders') }}/"+id+"/status", {_token:csrf, _method:'PATCH', status:next})
                .done(function(){
                    Swal.fire('Updated','','success');
                    table.ajax.reload(null,false);
                })
                .fail(function(xhr){
                    select.val(prev);
                    Swal.fire('Error', xhr.responseJSON?.message || 'Unable to update status', 'error');
                });
        });
    });
});
</script>
@endpush
