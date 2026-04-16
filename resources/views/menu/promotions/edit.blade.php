@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<style>
    .promo-card { border: 0; box-shadow: 0 4px 16px rgba(0,0,0,.05); }
    .promo-card .card-title { font-weight: 700; }
    .promo-header { background: linear-gradient(90deg, #2563eb, #4f46e5); color: #fff; }
</style>
@endpush

@section('content')
<div class="bg-light p-4 rounded shadow-sm border-0">
    <div class="promo-header rounded p-3 mb-3">
        <h1 class="h4 mb-1"><i class="fa-solid fa-ticket me-2"></i>{{ $page_title }}</h1>
        <div class="small">Update campaign rules, schedule and store targeting</div>
    </div>
    @include('layouts.partials.messages')
    <form method="POST" action="{{ route('menu.promotions.update', $promotion->id) }}" id="promotionForm">
        @csrf
        @method('PUT')
        <div class="card promo-card mb-3"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Campaign Name *</label><input name="name" class="form-control" value="{{ old('name', $promotion->name ?? '') }}" required></div>
                <div class="col-md-3"><label class="form-label">Coupon Code *</label><input name="code" class="form-control text-uppercase" value="{{ old('code', $promotion->code ?? '') }}" required></div>
                <div class="col-md-3"><label class="form-label">Offer Type *</label><select name="type" id="type" class="form-control" required>@foreach(\App\Models\Promotion::TYPES as $type)<option value="{{ $type }}" {{ old('type', $promotion->type ?? '') === $type ? 'selected' : '' }}>{{ strtoupper(str_replace('_',' ', $type)) }}</option>@endforeach</select></div>
                <div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description', $promotion->description ?? '') }}</textarea></div>
                <div class="col-md-12"><div class="alert alert-primary py-2 mb-0"><strong>Offer Preview:</strong> <span id="offer_preview_chip">Set values to see offer summary.</span></div></div>
                <div class="col-md-12">
                    <label class="form-label">Smart Preset</label>
                    <select id="smart_preset" class="form-control">
                        <option value="">Choose preset (optional)</option>
                        <option value="new_user_cart20">New user 20% up to 100 above 299</option>
                        <option value="weekend_flat75">Weekend flat 75 above 499</option>
                        <option value="lunch_rush_15">Lunch rush 15% category discount</option>
                        <option value="bxgy_classic">Buy 2 Get 1 classic</option>
                    </select>
                </div>
            </div>
        </div></div>

        <div class="card promo-card mb-3" id="discount_block"><div class="card-body">
            <h5 class="card-title"><i class="fa-solid fa-percent me-2"></i>Discount Rules</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Discount Value</label><input type="number" step="0.01" min="0" name="discount_value" class="form-control" value="{{ old('discount_value', $promotion->discount_value ?? '') }}"></div>
                <div class="col-md-3"><label class="form-label">Max Discount</label><input type="number" step="0.01" min="0" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount', $promotion->max_discount_amount ?? '') }}"></div>
                <div class="col-md-3"><label class="form-label">Min Cart Amount</label><input type="number" step="0.01" min="0" name="min_cart_amount" class="form-control" value="{{ old('min_cart_amount', $promotion->min_cart_amount ?? '') }}"></div>
                <div class="col-md-3"><label class="form-label">Priority</label><input type="number" min="0" name="priority" class="form-control" value="{{ old('priority', $promotion->priority ?? 0) }}"></div>
            </div>
        </div></div>

        <div class="card promo-card mb-3" id="bxgy_block"><div class="card-body">
            <h5 class="card-title"><i class="fa-solid fa-gift me-2"></i>Buy X Get Y</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Buy Product</label><select name="buy_product_id" class="form-control select2"><option value="">--</option>@foreach($products as $p)<option value="{{ $p->id }}" {{ (string)old('buy_product_id', $promotion->buy_product_id ?? '')===(string)$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Buy Qty</label><input type="number" min="1" name="buy_quantity" class="form-control" value="{{ old('buy_quantity', $promotion->buy_quantity ?? '') }}"></div>
                <div class="col-md-3"><label class="form-label">Get Product</label><select name="get_product_id" class="form-control select2"><option value="">--</option>@foreach($products as $p)<option value="{{ $p->id }}" {{ (string)old('get_product_id', $promotion->get_product_id ?? '')===(string)$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Get Qty</label><input type="number" step="1" min="1" name="get_quantity" class="form-control" value="{{ old('get_quantity', $promotion->get_quantity ?? '') }}"></div>
            </div>
        </div></div>

        <div class="card promo-card mb-3" id="scope_block"><div class="card-body">
            <h5 class="card-title"><i class="fa-solid fa-bullseye me-2"></i>Applicability & Scope</h5>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Applicable Categories</label><select name="applicable_category_ids[]" class="form-control select2" multiple>@foreach($categories as $c)<option value="{{ $c->id }}" {{ in_array((string)$c->id, array_map('strval', old('applicable_category_ids', $promotion->applicable_category_ids ?? [])), true) ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Applicable Products</label><select name="applicable_product_ids[]" class="form-control select2" multiple>@foreach($products as $p)<option value="{{ $p->id }}" {{ in_array((string)$p->id, array_map('strval', old('applicable_product_ids', $promotion->applicable_product_ids ?? [])), true) ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Scope</label><select name="is_global" id="is_global" class="form-control"><option value="1" {{ old('is_global', $promotion->is_global ?? 1) ? 'selected' : '' }}>All Stores</option><option value="0" {{ !old('is_global', $promotion->is_global ?? 1) ? 'selected' : '' }}>Store-wise</option></select></div>
                <div class="col-md-9" id="store_scope_wrap"><label class="form-label">Stores</label><select name="store_ids[]" class="form-control select2" multiple>@foreach($stores as $s)<option value="{{ $s->id }}" {{ in_array((string)$s->id, array_map('strval', old('store_ids', $selectedStores ?? [])), true) ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select></div>
            </div>
        </div></div>

        <div class="card promo-card mb-3" id="rule_builder_block">
            <div class="card-body">
                <h5 class="card-title"><i class="fa-solid fa-code-branch me-2"></i>Rule Builder (AND/OR)</h5>
                <div class="promo-help mb-2">Create eligibility conditions for this coupon. Example: cart amount AND payment method.</div>
                <div id="rule_rows"></div>
                <button type="button" id="add_rule_btn" class="btn btn-outline-primary btn-sm mt-2">+ Add Rule</button>
                <input type="hidden" name="rule_builder" id="rule_builder_json" value="{{ old('rule_builder', isset($promotion->meta['rule_builder']) ? json_encode($promotion->meta['rule_builder']) : '[]') }}">
            </div>
        </div>

        <div class="card promo-card mb-3"><div class="card-body">
            <h5 class="card-title"><i class="fa-regular fa-clock me-2"></i>Schedule, Limits & Flags</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Start</label><input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', isset($promotion) && $promotion->starts_at ? $promotion->starts_at->format('Y-m-d\TH:i') : '') }}"></div>
                <div class="col-md-3"><label class="form-label">End</label><input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', isset($promotion) && $promotion->ends_at ? $promotion->ends_at->format('Y-m-d\TH:i') : '') }}"></div>
                <div class="col-md-3"><label class="form-label">Total Usage Limit</label><input type="number" min="1" name="total_usage_limit" class="form-control" value="{{ old('total_usage_limit', $promotion->total_usage_limit ?? '') }}"></div>
                <div class="col-md-3"><label class="form-label">Per User Limit</label><input type="number" min="1" name="per_user_limit" class="form-control" value="{{ old('per_user_limit', $promotion->per_user_limit ?? '') }}"></div>
                <div class="col-md-3"><label class="form-label">Status</label><select name="is_active" class="form-control"><option value="1" {{ old('is_active', $promotion->is_active ?? 1) ? 'selected' : '' }}>Active</option><option value="0" {{ !old('is_active', $promotion->is_active ?? 1) ? 'selected' : '' }}>Inactive</option></select></div>
                <div class="col-md-3"><label class="form-label">Auto Apply</label><select name="is_auto_apply" class="form-control"><option value="0" {{ !old('is_auto_apply', $promotion->is_auto_apply ?? 0) ? 'selected' : '' }}>No</option><option value="1" {{ old('is_auto_apply', $promotion->is_auto_apply ?? 0) ? 'selected' : '' }}>Yes</option></select></div>
                <div class="col-md-3"><label class="form-label">Stackable</label><select name="is_stackable" class="form-control"><option value="0" {{ !old('is_stackable', $promotion->is_stackable ?? 0) ? 'selected' : '' }}>No</option><option value="1" {{ old('is_stackable', $promotion->is_stackable ?? 0) ? 'selected' : '' }}>Yes</option></select></div>
                <div class="col-md-3"><label class="form-label">First Order Discount % (Optional)</label><input type="number" name="get_discount_percent" step="0.01" min="0" max="100" class="form-control" value="{{ old('get_discount_percent', $promotion->get_discount_percent ?? '') }}"></div>
            </div>
            <div class="promo-help mt-2" id="inline_validation_hint"></div>
        </div></div>

        <div class="mt-3 d-flex gap-2"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save Promotion</button><a href="{{ route('menu.promotions.index') }}" class="btn btn-secondary">Back</a></div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
$(function(){
    $('.select2, #type, #is_global, select[name="is_active"], select[name="is_auto_apply"], select[name="is_stackable"]').select2({width:'100%'});
    function syncScope(){ $('#store_scope_wrap').toggle(String($('#is_global').val())==='0'); }
    function syncType() {
        const type = String($('#type').val() || '');
        const isBxgy = type === 'bxgy';
        const isCategoryOrProduct = ['category_flat','category_percent','product_flat','product_percent'].includes(type);
        $('#bxgy_block').toggle(isBxgy);
        $('#discount_block').toggle(!isBxgy || type === 'first_order');
        $('#scope_block').toggle(isCategoryOrProduct || type === 'bxgy');
    }
    syncScope(); syncType();
    $('#is_global').on('change', syncScope);
    $('#type').on('change', syncType);

    function readNum(name) {
        const v = parseFloat($('[name="'+name+'"]').val());
        return Number.isFinite(v) ? v : null;
    }
    function updatePreview() {
        const type = String($('#type').val() || '');
        const d = readNum('discount_value');
        const maxD = readNum('max_discount_amount');
        const minC = readNum('min_cart_amount');
        const buyQ = readNum('buy_quantity');
        const getQ = readNum('get_quantity');
        let text = 'Set values to see offer summary.';
        if (type === 'cart_percent' && d !== null) text = 'Flat ' + d + '% off' + (maxD !== null ? ' up to ' + maxD : '') + (minC !== null ? ' on orders above ' + minC : '') + '.';
        else if (type === 'cart_flat' && d !== null) text = 'Flat ' + d + ' off' + (minC !== null ? ' on orders above ' + minC : '') + '.';
        else if ((type === 'category_percent' || type === 'product_percent') && d !== null) text = d + '% off on selected ' + (type.indexOf('category') === 0 ? 'categories' : 'products') + (maxD !== null ? ' up to ' + maxD : '') + '.';
        else if ((type === 'category_flat' || type === 'product_flat') && d !== null) text = 'Flat ' + d + ' off on selected ' + (type.indexOf('category') === 0 ? 'categories.' : 'products.');
        else if (type === 'bxgy') text = 'Buy ' + (buyQ || 'X') + ' and get ' + (getQ || 'Y') + ' offer.';
        else if (type === 'free_delivery') text = 'Free delivery offer.';
        else if (type === 'first_order' && d !== null) text = 'First order: flat ' + d + (maxD !== null ? ' up to ' + maxD : '') + '.';
        $('#offer_preview_chip').text(text);
    }
    function hint(msg, isErr) { $('#inline_validation_hint').text(msg).toggleClass('text-danger', !!isErr).toggleClass('text-success', !isErr); }
    function validateHuman() {
        const type = String($('#type').val() || '');
        if (type === 'bxgy') {
            if (!$('[name="buy_product_id"]').val() || !$('[name="get_product_id"]').val() || !readNum('buy_quantity') || !readNum('get_quantity')) {
                hint('BXGY needs Buy Product, Buy Qty, Get Product and Get Qty.', true); return false;
            }
        }
        if ((type.indexOf('percent') !== -1) && readNum('discount_value') !== null && readNum('discount_value') > 100) {
            hint('Percentage discount cannot be greater than 100.', true); return false;
        }
        hint('Looks good. Promotion is valid for save.', false); return true;
    }
    function addRuleRow(row) {
        const data = row || { condition: 'cart_amount', operator: '>=', value: '', logic: 'AND' };
        const idx = $('#rule_rows .rule-row').length;
        const html = '<div class="row g-2 align-items-end rule-row mb-2">' +
            '<div class="col-md-3"><label class="form-label">If</label><select class="form-control rule-condition"><option value="cart_amount">Cart Amount</option><option value="item_count">Item Count</option><option value="payment_method">Payment Method</option><option value="user_type">User Type</option><option value="day_of_week">Day of Week</option></select></div>' +
            '<div class="col-md-2"><label class="form-label">Operator</label><select class="form-control rule-operator"><option value=">=">>=</option><option value="<="><=</option><option value="=">=</option><option value="!=">!=</option><option value="in">IN</option></select></div>' +
            '<div class="col-md-3"><label class="form-label">Value</label><input type="text" class="form-control rule-value" value=""></div>' +
            '<div class="col-md-2"><label class="form-label">Join</label><select class="form-control rule-logic"><option value="AND">AND</option><option value="OR">OR</option></select></div>' +
            '<div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm rule-remove">Remove</button></div>' +
            '</div>';
        $('#rule_rows').append(html);
        const r = $('#rule_rows .rule-row').eq(idx);
        r.find('.rule-condition').val(data.condition);
        r.find('.rule-operator').val(data.operator);
        r.find('.rule-value').val(data.value);
        r.find('.rule-logic').val(data.logic);
    }
    function syncRuleJson() {
        const rows = [];
        $('#rule_rows .rule-row').each(function(){
            rows.push({ condition: $(this).find('.rule-condition').val(), operator: $(this).find('.rule-operator').val(), value: $(this).find('.rule-value').val(), logic: $(this).find('.rule-logic').val() });
        });
        $('#rule_builder_json').val(JSON.stringify(rows));
    }
    function applyPreset(key) {
        if (!key) return;
        if (key === 'new_user_cart20') { $('#type').val('cart_percent').trigger('change'); $('[name="discount_value"]').val('20'); $('[name="max_discount_amount"]').val('100'); $('[name="min_cart_amount"]').val('299'); $('#rule_rows').empty(); addRuleRow({condition:'user_type',operator:'=',value:'new',logic:'AND'}); }
        else if (key === 'weekend_flat75') { $('#type').val('cart_flat').trigger('change'); $('[name="discount_value"]').val('75'); $('[name="min_cart_amount"]').val('499'); $('#rule_rows').empty(); addRuleRow({condition:'day_of_week',operator:'in',value:'sat,sun',logic:'AND'}); }
        else if (key === 'lunch_rush_15') { $('#type').val('category_percent').trigger('change'); $('[name="discount_value"]').val('15'); $('[name="max_discount_amount"]').val('120'); $('#rule_rows').empty(); addRuleRow({condition:'day_of_week',operator:'in',value:'mon,tue,wed,thu,fri',logic:'AND'}); }
        else if (key === 'bxgy_classic') { $('#type').val('bxgy').trigger('change'); $('[name="buy_quantity"]').val('2'); $('[name="get_quantity"]').val('1'); $('#rule_rows').empty(); addRuleRow(); }
        updatePreview(); validateHuman(); syncRuleJson();
    }
    try {
        const existingRules = JSON.parse($('#rule_builder_json').val() || '[]');
        if (Array.isArray(existingRules) && existingRules.length) existingRules.forEach(addRuleRow); else addRuleRow();
    } catch (e) { addRuleRow(); }
    $('#add_rule_btn').on('click', function(){ addRuleRow(); syncRuleJson(); });
    $(document).on('click', '.rule-remove', function(){ $(this).closest('.rule-row').remove(); syncRuleJson(); });
    $(document).on('change keyup', '.rule-condition,.rule-operator,.rule-value,.rule-logic', syncRuleJson);
    $('#smart_preset').on('change', function(){ applyPreset($(this).val()); });
    $('#type, [name="discount_value"], [name="max_discount_amount"], [name="min_cart_amount"], [name="buy_quantity"], [name="get_quantity"], [name="buy_product_id"], [name="get_product_id"]').on('change keyup', function(){ updatePreview(); validateHuman(); });
    $('#promotionForm').on('submit', function(e){ syncRuleJson(); if (!validateHuman()) { e.preventDefault(); Swal.fire('Validation', 'Please resolve highlighted rule hints before saving.', 'warning'); } });
    updatePreview(); validateHuman();
});
</script>
@endpush
