@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
@endpush

@section('content')
<div class="bg-light p-4 rounded shadow-sm border-0">
    <h1 class="h3 mb-3"><i class="fa-solid fa-pen-to-square me-2"></i>{{ $page_title }}</h1>
    @include('layouts.partials.messages')
    <form method="POST" action="{{ route('menu.attributes.store') }}" id="attrForm" class="mt-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="191" required>
            @error('name')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
        </div>
        <h5 class="mt-4">Values</h5>
        <div id="value-rows"></div>
        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="add-value-row">Add Value</button>
        <div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('menu.attributes.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
let valueIndex = 0;
function addRow(data) {
    data = data || { value: '', extra_price: '0', ordering: '0' };
    const html = `
    <div class="row g-2 align-items-end value-row mb-2" data-idx="${valueIndex}">
        <div class="col-md-4">
            <label class="form-label small">Value</label>
            <input type="text" name="values[${valueIndex}][value]" class="form-control" value="${data.value || ''}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Extra price</label>
            <input type="number" step="0.01" min="0" name="values[${valueIndex}][extra_price]" class="form-control" value="${data.extra_price}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Ordering</label>
            <input type="number" name="values[${valueIndex}][ordering]" class="form-control" value="${data.ordering}">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-sm remove-value">Remove</button>
        </div>
    </div>`;
    $('#value-rows').append(html);
    valueIndex++;
}
$(function () {
    $('select.form-control, select').select2({ width: '100%' });
    addRow();
    $('#add-value-row').on('click', function () { addRow(); });
    $(document).on('click', '.remove-value', function () {
        if ($('.value-row').length <= 1) return;
        $(this).closest('.value-row').remove();
    });
    $('#attrForm').validate({
        rules: { name: { required: true, maxlength: 191 } },
        errorPlacement: function (e, el) { e.addClass('text-danger small d-block mt-1'); e.insertAfter(el); },
        highlight: function (el) { $(el).addClass('is-invalid'); },
        unhighlight: function (el) { $(el).removeClass('is-invalid'); }
    });
});
</script>
@endpush
