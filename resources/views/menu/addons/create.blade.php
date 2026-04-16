@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
@endpush

@section('content')
<div class="bg-light p-4 rounded shadow-sm border-0">
    <h1 class="h3 mb-3"><i class="fa-solid fa-pen-to-square me-2"></i>{{ $page_title }}</h1>
    @include('layouts.partials.messages')
    <form method="POST" action="{{ route('menu.addons.store') }}" id="addonForm" class="mt-3">
        @csrf
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>Add-on Values</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addAddonRow">
                        <i class="fa-solid fa-plus me-1"></i>Add Row
                    </button>
                </div>
                <div id="addonRows"></div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('menu.addons.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
let addonIndex = 0;

function addonRow(row) {
    const data = row || { name: '', price: '0.00', description: '', status: '1' };
    const html = `
    <div class="border rounded p-3 mb-2 addon-row">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="addons[${addonIndex}][name]" class="form-control addon-name" maxlength="191" value="${data.name}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Price <span class="text-danger">*</span></label>
                <input type="number" name="addons[${addonIndex}][price]" class="form-control addon-price" step="0.01" min="0" value="${data.price}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Description</label>
                <input type="text" name="addons[${addonIndex}][description]" class="form-control" value="${data.description}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="addons[${addonIndex}][status]" class="form-control addon-status">
                    <option value="1" ${String(data.status) === '1' ? 'selected' : ''}>Active</option>
                    <option value="0" ${String(data.status) === '0' ? 'selected' : ''}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm w-100 remove-addon-row"><i class="fa-solid fa-trash me-1"></i>Remove</button>
            </div>
        </div>
    </div>`;
    $('#addonRows').append(html);
    addonIndex++;
    $('#addonRows .addon-status').last().select2({ width: '100%' });
}

$(function () {
    addonRow();
    $('#addAddonRow').on('click', function () { addonRow(); });
    $(document).on('click', '.remove-addon-row', function () {
        if ($('.addon-row').length <= 1) return;
        $(this).closest('.addon-row').remove();
    });

$('#addonForm').validate({
    ignore: [],
    errorPlacement: function (e, el) { e.addClass('text-danger small d-block mt-1'); e.insertAfter(el); },
    highlight: function (el) { $(el).addClass('is-invalid'); },
    unhighlight: function (el) { $(el).removeClass('is-invalid'); }
});
});
</script>
@endpush
