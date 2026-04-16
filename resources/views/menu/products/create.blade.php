@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
@endpush

@section('content')
<div class="bg-light p-4 rounded shadow-sm border-0">
    <h1 class="h3 mb-3"><i class="fa-solid fa-burger me-2"></i>{{ $page_title }}</h1>
    @include('layouts.partials.messages')

    <form method="POST" action="{{ route('menu.products.store') }}" enctype="multipart/form-data" id="productForm" class="mt-3">
        @csrf
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa-solid fa-circle-info me-2"></i>Primary Details</h5>

                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="191" required>
                            @error('name')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" maxlength="191" placeholder="Auto from product name if empty">
                            @error('slug')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            @error('description')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Base Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="base_price" class="form-control" value="{{ old('base_price', '0') }}" required>
                            @error('base_price')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" id="product_image">
                            <div id="img_preview" class="mt-2"></div>
                            @error('image')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ordering</label>
                            <input type="number" name="ordering" class="form-control" value="{{ old('ordering', 0) }}" min="0" required>
                            @error('ordering')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa-solid fa-sliders me-2"></i>Product Attributes</h5>
                        <label class="form-label">Select Attribute</label>
                        <select id="attribute_selector" class="form-control" multiple>
                            @foreach($attributes as $attr)
                                <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                            @endforeach
                        </select>
                        <div id="attributeBlocks" class="mt-3"></div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa-solid fa-puzzle-piece me-2"></i>Product Add-ons</h5>
                        <label class="form-label">Select Add-on</label>
                        <select id="addon_selector" class="form-control" multiple>
                            @foreach($addons as $addon)
                                <option value="{{ $addon->id }}">{{ $addon->name }}</option>
                            @endforeach
                        </select>
                        <div id="addonBlocks" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
        <a href="{{ route('menu.products.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
<script type="application/json" id="attributes-json-create">@json($attributes)</script>
<script type="application/json" id="addons-json-create">@json($addons)</script>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
const attributesData = JSON.parse(document.getElementById('attributes-json-create').textContent || '[]');
const addonsData = JSON.parse(document.getElementById('addons-json-create').textContent || '[]');
const attributeState = {};
const addonState = {};

function ensureAttributeState(attributeId) {
    const attr = attributesData.find(a => String(a.id) === String(attributeId));
    if (!attr) return;
    if (!attributeState[attributeId]) attributeState[attributeId] = { values: {} };
    (attr.values || []).forEach(v => {
        if (!attributeState[attributeId].values[v.id]) {
            attributeState[attributeId].values[v.id] = { price_override: '', is_available: true, is_default: false };
        }
    });
}

function renderAttributes() {
    let idx = 0, html = '';
    Object.keys(attributeState).forEach(aid => {
        const attr = attributesData.find(a => String(a.id) === String(aid));
        if (!attr) return;
        html += '<h6 class="fw-bold border-bottom pb-2 mt-2">' + $('<div>').text(attr.name).html() + '</h6>';
        html += '<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Value</th><th>Additional Price</th><th>Availability</th><th>Default</th><th>Action</th></tr></thead><tbody>';
        Object.keys(attributeState[aid].values).forEach(vid => {
            const valueObj = (attr.values || []).find(v => String(v.id) === String(vid));
            if (!valueObj) return;
            const row = attributeState[aid].values[vid];
            html += '<tr data-aid="' + aid + '" data-vid="' + vid + '">';
            html += '<td>' + $('<div>').text(valueObj.value).html() + '<input type="hidden" name="product_attributes[' + idx + '][attribute_id]" value="' + aid + '"><input type="hidden" name="product_attributes[' + idx + '][attribute_value_id]" value="' + vid + '"></td>';
            html += '<td><input type="number" step="0.01" min="0" class="form-control attr-price" name="product_attributes[' + idx + '][price_override]" value="' + (row.price_override ?? '') + '"></td>';
            html += '<td><select class="form-control attr-available" name="product_attributes[' + idx + '][is_available]"><option value="1"' + (row.is_available ? ' selected' : '') + '>Available</option><option value="0"' + (!row.is_available ? ' selected' : '') + '>Unavailable</option></select></td>';
            html += '<td><input type="checkbox" class="form-check-input attr-default" name="product_attributes[' + idx + '][is_default]" value="1"' + (row.is_default ? ' checked' : '') + '></td>';
            html += '<td><button type="button" class="btn btn-danger btn-sm remove-attr-row">Remove</button></td>';
            html += '</tr>';
            idx++;
        });
        html += '</tbody></table></div>';
    });
    $('#attributeBlocks').html(html);
    $('#attributeBlocks .attr-available').select2({ width: '100%', minimumResultsForSearch: Infinity });
}

function renderAddons() {
    let idx = 0;
    let html = '<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Value</th><th>Additional Price</th><th>Availability</th><th>Default</th><th>Action</th></tr></thead><tbody>';
    Object.keys(addonState).forEach(id => {
        const addon = addonsData.find(a => String(a.id) === String(id));
        if (!addon) return;
        const row = addonState[id];
        html += '<tr data-addon-id="' + id + '">';
        html += '<td>' + $('<div>').text(addon.name).html() + '<input type="hidden" name="product_addons[' + idx + '][addon_id]" value="' + id + '"></td>';
        html += '<td><input type="number" step="0.01" min="0" class="form-control addon-price" name="product_addons[' + idx + '][price_override]" value="' + (row.price_override ?? '') + '"></td>';
        html += '<td><select class="form-control addon-available" name="product_addons[' + idx + '][is_available]"><option value="1"' + (row.is_available ? ' selected' : '') + '>Available</option><option value="0"' + (!row.is_available ? ' selected' : '') + '>Unavailable</option></select></td>';
        html += '<td><input type="checkbox" class="form-check-input addon-default" name="product_addons[' + idx + '][is_default]" value="1"' + (row.is_default ? ' checked' : '') + '></td>';
        html += '<td><button type="button" class="btn btn-danger btn-sm remove-addon-row">Remove</button></td>';
        html += '</tr>';
        idx++;
    });
    html += '</tbody></table></div>';
    $('#addonBlocks').html(html);
    $('#addonBlocks .addon-available').select2({ width: '100%', minimumResultsForSearch: Infinity });
}

$(function () {
    $('select[name="category_id"], select[name="status"], #attribute_selector, #addon_selector').select2({ width: '100%' });

    $('#attribute_selector').on('change', function () {
        const selected = ($(this).val() || []).map(String);
        Object.keys(attributeState).forEach(aid => { if (!selected.includes(String(aid))) delete attributeState[aid]; });
        selected.forEach(aid => ensureAttributeState(aid));
        renderAttributes();
    });
    $('#addon_selector').on('change', function () {
        const selected = ($(this).val() || []).map(String);
        Object.keys(addonState).forEach(id => { if (!selected.includes(String(id))) delete addonState[id]; });
        selected.forEach(id => { if (!addonState[id]) addonState[id] = { price_override: '', is_available: true, is_default: false }; });
        renderAddons();
    });

    $(document).on('input', '.attr-price', function () {
        const tr = $(this).closest('tr'); const aid = tr.data('aid'); const vid = tr.data('vid');
        if (attributeState[aid] && attributeState[aid].values[vid]) attributeState[aid].values[vid].price_override = $(this).val();
    });
    $(document).on('change', '.attr-available', function () {
        const tr = $(this).closest('tr'); const aid = tr.data('aid'); const vid = tr.data('vid');
        if (attributeState[aid] && attributeState[aid].values[vid]) attributeState[aid].values[vid].is_available = String($(this).val()) === '1';
    });
    $(document).on('change', '.attr-default', function () {
        const tr = $(this).closest('tr'); const aid = tr.data('aid'); const vid = tr.data('vid');
        if (attributeState[aid] && attributeState[aid].values[vid]) attributeState[aid].values[vid].is_default = $(this).is(':checked');
    });
    $(document).on('input', '.addon-price', function () {
        const id = String($(this).closest('tr').data('addon-id')); if (addonState[id]) addonState[id].price_override = $(this).val();
    });
    $(document).on('change', '.addon-available', function () {
        const id = String($(this).closest('tr').data('addon-id')); if (addonState[id]) addonState[id].is_available = String($(this).val()) === '1';
    });
    $(document).on('change', '.addon-default', function () {
        const id = String($(this).closest('tr').data('addon-id')); if (addonState[id]) addonState[id].is_default = $(this).is(':checked');
    });

    $(document).on('click', '.remove-attr-row', function () {
        const tr = $(this).closest('tr'); const aid = tr.data('aid'); const vid = tr.data('vid');
        Swal.fire({ title: 'Remove this attribute value?', icon: 'warning', showCancelButton: true }).then(r => {
            if (!r.isConfirmed) return;
            if (attributeState[aid]) {
                delete attributeState[aid].values[vid];
                if (!Object.keys(attributeState[aid].values).length) {
                    delete attributeState[aid];
                    const selected = ($('#attribute_selector').val() || []).filter(v => String(v) !== String(aid));
                    $('#attribute_selector').val(selected).trigger('change');
                    return;
                }
            }
            renderAttributes();
        });
    });
    $(document).on('click', '.remove-addon-row', function () {
        const id = String($(this).closest('tr').data('addon-id'));
        Swal.fire({ title: 'Remove this add-on?', icon: 'warning', showCancelButton: true }).then(r => {
            if (!r.isConfirmed) return;
            delete addonState[id];
            const selected = ($('#addon_selector').val() || []).filter(v => String(v) !== id);
            $('#addon_selector').val(selected).trigger('change');
        });
    });

    $('#product_image').on('change', function () {
        const f = this.files && this.files[0];
        $('#img_preview').empty();
        if (!f || !f.type.match('image')) return;
        const r = new FileReader();
        r.onload = function (e) { $('#img_preview').html('<img src="' + e.target.result + '" class="img-thumbnail" style="max-height:120px">'); };
        r.readAsDataURL(f);
    });
});
</script>
@endpush
