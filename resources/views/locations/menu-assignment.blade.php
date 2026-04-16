@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<style>
    .menu-col-card { min-height: 620px; }
    .category-list .cat-row {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 10px 12px;
        cursor: pointer;
        margin-bottom: 8px;
        background: #fff;
        transition: all .15s ease;
    }
    .category-list .cat-row.active {
        background: linear-gradient(135deg, #4ea1ff, #2d7be8);
        color: #fff;
        border-color: #2d7be8;
        box-shadow: 0 4px 12px rgba(45, 123, 232, .25);
    }
    .category-list .cat-row .cat-check { margin-right: 8px; }
    .product-scroll, .option-scroll { max-height: 520px; overflow-y: auto; }
    .option-section-title { font-size: 1.1rem; font-weight: 700; color: #1f2937; }
    .option-group-title { font-size: 1.05rem; font-weight: 700; margin: 12px 0 6px; color: #212529; }
    .option-row {
        border: 1px solid #edf1f5;
        border-radius: 8px;
        padding: 9px 10px;
        margin-bottom: 7px;
        background: #fff;
    }
    .availability-pill {
        border: 0;
        border-radius: 999px;
        padding: 4px 14px;
        min-width: 118px;
        font-weight: 600;
        color: #fff;
    }
    .availability-pill.available { background: #35b96e; }
    .availability-pill.unavailable { background: #e05b65; }
</style>
@endpush

@section('content')
<div class="bg-light p-4 rounded shadow-sm border-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h3 mb-0"><i class="fa-solid fa-store me-2"></i>{{ $page_title }}</h1>
            <p class="text-muted mb-0">{{ $location->name }}</p>
        </div>
        <a href="{{ route('stores.edit', $location->id) }}" class="btn btn-outline-secondary btn-sm">Back to location</a>
    </div>
    @include('layouts.partials.messages')

    <form method="POST" action="{{ route('locations.menu-assignment.save', $location->id) }}" id="menuAssignForm">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 menu-col-card">
                    <div class="card-body">
                        <h5 class="card-title mb-2"><i class="fa-solid fa-layer-group me-2"></i>Categories</h5>
                        <input type="text" class="form-control form-control-sm mb-2" id="cat_search" placeholder="Search categories...">
                        <div id="cat_list" class="category-list product-scroll">
                            @foreach($categories as $cat)
                                <div class="cat-row cat-item {{ in_array($cat->id, $categoryIds, true) ? 'active' : '' }}" data-name="{{ strtolower($cat->name) }}" data-id="{{ $cat->id }}">
                                    <label class="w-100 d-flex justify-content-between align-items-center mb-0" for="cc{{ $cat->id }}">
                                        <span class="d-flex align-items-center">
                                            <input class="form-check-input cat-cb cat-check" type="checkbox" value="{{ $cat->id }}" id="cc{{ $cat->id }}"
                                                {{ in_array($cat->id, $categoryIds, true) ? 'checked' : '' }}>
                                            <span>{{ $cat->name }}</span>
                                        </span>
                                        <i class="fa-solid fa-check"></i>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="card shadow-sm border-0 menu-col-card">
                            <div class="card-body">
                                <h5 class="card-title mb-2"><i class="fa-solid fa-utensils me-2"></i>Products</h5>
                                <div id="products_panel" class="product-scroll border rounded p-3 bg-white">
                                    <span class="text-muted">Select a category to load products.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 menu-col-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0 option-section-title"><i class="fa-solid fa-sliders me-2"></i><span id="config_title">Product Options</span></h5>
                                </div>
                                <select id="config_product_selector" class="form-control form-control-sm mb-2">
                                    <option value="">Select product to configure</option>
                                </select>
                                <input type="text" id="option_search" class="form-control form-control-sm mb-2 mt-2" placeholder="Search options...">
                                <div id="product_config_panel" class="option-scroll border rounded p-3 bg-white">
                                    <span class="text-muted">Choose a selected product to configure availability/default options.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            @can('store_menu.manage')
                <button type="submit" class="btn btn-primary">Save assignment</button>
            @endcan
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
const productsUrl = "{{ route('locations.menu-assignment.products', $location->id) }}";
const productConfigUrl = "{{ route('locations.menu-assignment.product-config', $location->id) }}";
const assignState = {};
const productConfigState = {};
let activeConfigProductId = '';

function refreshConfigSelector() {
    const sel = $('#config_product_selector');
    const current = sel.val();
    let options = '<option value="">Select product to configure</option>';
    Object.keys(assignState).forEach(cid => {
        (assignState[cid].products || []).forEach(p => {
            if (p.selected) options += '<option value="' + p.id + '">' + $('<div>').text(p.name).html() + '</option>';
        });
    });
    sel.html(options).val(current).trigger('change.select2');
}

$('#cat_search').on('keyup', function () {
    const q = $(this).val().toLowerCase();
    $('.cat-item').each(function () {
        $(this).toggle($(this).data('name').indexOf(q) !== -1);
    });
});

function getSelectedCategoryIds() {
    return $('.cat-cb:checked').toArray().map(el => String($(el).val()));
}

function loadProductsForSelected() {
    const selectedCategoryIds = getSelectedCategoryIds();
    if (!selectedCategoryIds.length) {
        $('#products_panel').html('<span class="text-muted">Select a category to load products.</span>');
        assignStateClear();
        refreshConfigSelector();
        return;
    }

    $('#products_panel').html('<span class="text-muted">Loading…</span>');

    $.get(productsUrl, { category_ids: selectedCategoryIds }, function (res) {
        const categories = res.categories || [];
        const newState = {};
        let html = '';

        categories.forEach(function (cat) {
            const cid = String(cat.id);
            const existing = assignState[cid];
            const fallbackState = {
                select_all: !!cat.select_all,
                products: (cat.products || []).map(p => ({ id: String(p.id), name: p.name, selected: !!p.selected }))
            };
            newState[cid] = existing || fallbackState;

            html += '<div class="border rounded p-2 mb-2">';
            html += '<div class="d-flex justify-content-between align-items-center mb-1">';
            html += '<strong class="small">' + $('<div>').text(cat.name).html() + '</strong>';
            html += '<label class="form-check-label small"><input type="checkbox" class="form-check-input me-1 sel-all-cat" data-cat="' + cid + '"' + (newState[cid].select_all ? ' checked' : '') + '>Select all</label>';
            html += '</div>';
            (newState[cid].products || []).forEach(function (p) {
                html += '<div class="form-check ms-1"><input class="form-check-input prod-cb" type="checkbox" data-cat="' + cid + '" value="' + p.id + '" id="p' + cid + '_' + p.id + '"' + (p.selected ? ' checked' : '') + (newState[cid].select_all ? ' disabled' : '') + '><label class="form-check-label" for="p' + cid + '_' + p.id + '">' + $('<div>').text(p.name).html() + '</label></div>';
            });
            html += '</div>';
        });

        assignStateClear();
        Object.keys(newState).forEach(function (cid) { assignState[cid] = newState[cid]; });

        $('#products_panel').html(html || '<span class="text-muted">No products found for selected categories.</span>');
        refreshConfigSelector();
    });
}

function assignStateClear() {
    Object.keys(assignState).forEach(function (cid) {
        if (!getSelectedCategoryIds().includes(String(cid))) {
            delete assignState[cid];
        }
    });
}

$(document).on('change', '.cat-cb', function () {
    const id = String($(this).val());
    if ($(this).is(':checked')) {
        $('.cat-row[data-id="' + id + '"]').addClass('active');
    } else {
        $('.cat-row[data-id="' + id + '"]').removeClass('active');
    }
    loadProductsForSelected();
});

$(document).on('change', '.sel-all-cat', function () {
    const cid = String($(this).data('cat'));
    const on = $(this).is(':checked');
    const checks = $('.prod-cb[data-cat="' + cid + '"]');
    checks.prop('checked', on).prop('disabled', on);
    if (assignState[cid]) {
        assignState[cid].select_all = on;
        assignState[cid].products.forEach(p => { p.selected = on; });
    }
    refreshConfigSelector();
});

$(document).on('change', '.prod-cb', function () {
    const cid = String($(this).data('cat'));
    const pid = String($(this).val());
    const on = $(this).is(':checked');
    if (assignState[cid]) {
        const row = assignState[cid].products.find(p => p.id === pid);
        if (row) row.selected = on;
    }
    refreshConfigSelector();
});

$('#config_product_selector').select2({ width: '100%' }).on('change', function () {
    const pid = String($(this).val() || '');
    activeConfigProductId = pid;
    if (!pid) {
        $('#product_config_panel').html('<span class="text-muted">Choose a selected product to configure availability/default options.</span>');
        return;
    }
    if (productConfigState[pid]) {
        const selectedText = $('#config_product_selector option:selected').text();
        $('#config_title').text((selectedText || 'Product') + ' Options');
        renderConfigPanel(pid);
        return;
    }
    $('#product_config_panel').html('<span class="text-muted">Loading configuration…</span>');
    $.get(productConfigUrl, { product_id: pid }, function (res) {
        const selectedText = $('#config_product_selector option:selected').text();
        $('#config_title').text((selectedText || 'Product') + ' Options');
        productConfigState[pid] = { attributes: res.attributes || [], addons: res.addons || [] };
        renderConfigPanel(pid);
    });
});

function renderConfigPanel(pid) {
    const data = productConfigState[pid] || { attributes: [], addons: [] };
    let html = '<div class="option-group-title">Attributes</div>';
    data.attributes.forEach((r, i) => {
        const group = String(r.attribute_name || 'attribute').replace(/[^a-zA-Z0-9_]/g, '_');
        const availClass = r.is_available ? 'available' : 'unavailable';
        const availText = r.is_available ? 'Available' : 'Unavailable';
        html += '<div class="option-row cfg-row" data-filter="' + ((r.attribute_name || '') + ' ' + (r.value_name || '')).toLowerCase() + '" data-type="attr" data-index="' + i + '">';
        html += '<div class="d-flex justify-content-between align-items-center"><div><div class="fw-semibold">' + $('<div>').text(r.value_name || '').html() + '</div><div class="small text-muted">' + $('<div>').text(r.attribute_name || '').html() + '</div></div>';
        html += '<div class="d-flex align-items-center gap-2"><button type="button" class="availability-pill cfg-avail-pill ' + availClass + '">' + availText + '</button>';
        html += '<input type="radio" class="form-check-input cfg-default-attr" name="default_attr_' + group + '" ' + (r.is_default ? 'checked' : '') + '></div></div></div>';
    });
    html += '<div class="option-group-title mt-3">Add-ons</div>';
    data.addons.forEach((r, i) => {
        const availClass = r.is_available ? 'available' : 'unavailable';
        const availText = r.is_available ? 'Available' : 'Unavailable';
        html += '<div class="option-row cfg-row" data-filter="' + (r.addon_name || '').toLowerCase() + '" data-type="addon" data-index="' + i + '">';
        html += '<div class="d-flex justify-content-between align-items-center"><div class="fw-semibold">' + $('<div>').text(r.addon_name || '').html() + '</div>';
        html += '<div class="d-flex align-items-center gap-2"><button type="button" class="availability-pill cfg-avail-pill ' + availClass + '">' + availText + '</button>';
        html += '<input type="checkbox" class="form-check-input cfg-default-addon" ' + (r.is_default ? 'checked' : '') + '></div></div></div>';
    });
    $('#product_config_panel').html(html);
}

$(document).on('click', '.cat-row', function (e) {
    if ($(e.target).is('input')) return;
    const id = String($(this).data('id'));
    const cb = $('#cc' + id);
    cb.prop('checked', !cb.is(':checked')).trigger('change');
});

$(document).on('click', '#product_config_panel .cfg-avail-pill', function () {
    const row = $(this).closest('.cfg-row');
    const type = row.data('type');
    const idx = parseInt(row.data('index'), 10);
    const arr = type === 'attr' ? productConfigState[activeConfigProductId].attributes : productConfigState[activeConfigProductId].addons;
    if (!arr[idx]) return;
    arr[idx].is_available = !arr[idx].is_available;
    const isAvail = arr[idx].is_available;
    $(this).toggleClass('available', isAvail).toggleClass('unavailable', !isAvail).text(isAvail ? 'Available' : 'Unavailable');
});

$(document).on('change', '#product_config_panel .cfg-default-attr, #product_config_panel .cfg-default-addon', function () {
    const rowEl = $(this).closest('.cfg-row');
    const type = rowEl.data('type');
    const idx = parseInt(rowEl.data('index'), 10);
    const arr = type === 'attr' ? productConfigState[activeConfigProductId].attributes : productConfigState[activeConfigProductId].addons;
    if (!arr[idx]) return;
    if (type === 'attr') {
        const attrName = arr[idx].attribute_name || '';
        arr.forEach(item => {
            if ((item.attribute_name || '') === attrName) item.is_default = false;
        });
        arr[idx].is_default = rowEl.find('.cfg-default-attr').is(':checked');
    } else {
        arr[idx].is_default = rowEl.find('.cfg-default-addon').is(':checked');
    }
});

$('#option_search').on('keyup', function () {
    const q = ($(this).val() || '').toLowerCase();
    $('.cfg-row').each(function () {
        const hay = String($(this).data('filter') || '');
        $(this).toggle(hay.indexOf(q) !== -1);
    });
});

loadProductsForSelected();

$('#menuAssignForm').on('submit', function (e) {
    e.preventDefault();
    const form = $(this);
    form.find('input[name^="categories"], input[name^="product_configs"]').remove();
    let i = 0;
    Object.keys(assignState).forEach(function (cid) {
        const st = assignState[cid] || { select_all: false, products: [] };
        const selectedIds = (st.products || []).filter(p => p.selected).map(p => p.id);
        $('<input>').attr({ type: 'hidden', name: 'categories[' + i + '][category_id]', value: cid }).appendTo(form);
        $('<input>').attr({ type: 'hidden', name: 'categories[' + i + '][select_all]', value: st.select_all ? '1' : '0' }).appendTo(form);
        selectedIds.forEach(function (pid, j) {
            $('<input>').attr({ type: 'hidden', name: 'categories[' + i + '][product_ids][' + j + ']', value: pid }).appendTo(form);
        });
        i++;
    });
    Object.keys(productConfigState).forEach(function (pid) {
        const block = productConfigState[pid] || {};
        (block.attributes || []).forEach(function (r, idx) {
            $('<input>').attr({ type: 'hidden', name: 'product_configs[' + pid + '][attributes][' + idx + '][product_attribute_id]', value: r.product_attribute_id }).appendTo(form);
            $('<input>').attr({ type: 'hidden', name: 'product_configs[' + pid + '][attributes][' + idx + '][is_available]', value: r.is_available ? '1' : '0' }).appendTo(form);
            $('<input>').attr({ type: 'hidden', name: 'product_configs[' + pid + '][attributes][' + idx + '][is_default]', value: r.is_default ? '1' : '0' }).appendTo(form);
        });
        (block.addons || []).forEach(function (r, idx) {
            $('<input>').attr({ type: 'hidden', name: 'product_configs[' + pid + '][addons][' + idx + '][product_addon_id]', value: r.product_addon_id }).appendTo(form);
            $('<input>').attr({ type: 'hidden', name: 'product_configs[' + pid + '][addons][' + idx + '][is_available]', value: r.is_available ? '1' : '0' }).appendTo(form);
            $('<input>').attr({ type: 'hidden', name: 'product_configs[' + pid + '][addons][' + idx + '][is_default]', value: r.is_default ? '1' : '0' }).appendTo(form);
        });
    });
    form.off('submit');
    form.get(0).submit();
});
</script>
@endpush
