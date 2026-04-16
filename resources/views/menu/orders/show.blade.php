@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
<style>
  /* ── Tailwind scope: prefix tw- to avoid Bootstrap conflict ── */
  /* We use a data attribute to scope our custom styles */
  [data-ofs] * { box-sizing: border-box; }

  [data-ofs] {
    font-family: 'DM Sans', sans-serif;
    background: #F5F6F8;
    min-height: 100vh;
    padding: 2rem 1.5rem;
  }

  /* ── Page header ── */
  .ofs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
  }
  .ofs-header h1 {
    font-size: 1.35rem;
    font-weight: 600;
    color: #0E0F11;
    letter-spacing: -0.02em;
    margin: 0;
  }
  .ofs-breadcrumb {
    font-size: 0.78rem;
    color: #8A8F9C;
    margin-top: 2px;
  }

  /* ── Section card ── */
  .ofs-card {
    background: #ffffff;
    border: 1px solid #E8EAF0;
    border-radius: 14px;
    margin-bottom: 1.25rem;
    overflow: hidden;
  }
  .ofs-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid #F0F2F7;
    background: #FAFBFD;
  }
  .ofs-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .ofs-card-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
  }
  .ofs-card-icon.blue  { background: #EEF4FF; color: #3B6ECC; }
  .ofs-card-icon.green { background: #EDFAF3; color: #1D8A55; }
  .ofs-card-icon.amber { background: #FFF8EC; color: #B06F10; }
  .ofs-card-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: #1A1C22;
    margin: 0;
  }
  .ofs-card-subtitle {
    font-size: 0.75rem;
    color: #8A8F9C;
    margin: 0;
  }
  .ofs-card-body {
    padding: 1.4rem 1.5rem;
  }

  /* ── Field grid ── */
  .ofs-field-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
  }

  /* ── Field label + control ── */
  .ofs-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #5A5F6E;
    margin-bottom: 5px;
    letter-spacing: 0.01em;
  }
  .ofs-label .req { color: #D94E4E; margin-left: 1px; }

  /* Override Bootstrap form-control within our scope */
  [data-ofs] .form-control,
  [data-ofs] .select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #DDE0EA !important;
    border-radius: 8px !important;
    background: #FAFBFD !important;
    font-size: 0.84rem !important;
    color: #1A1C22 !important;
    padding: 0 12px !important;
    box-shadow: none !important;
    transition: border-color 0.15s, box-shadow 0.15s;
    line-height: 38px !important;
  }
  [data-ofs] .form-control:focus {
    border-color: #3B6ECC !important;
    box-shadow: 0 0 0 3px rgba(59, 110, 204, 0.12) !important;
    background: #fff !important;
  }
  [data-ofs] .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3B6ECC !important;
    box-shadow: 0 0 0 3px rgba(59, 110, 204, 0.12) !important;
  }
  [data-ofs] .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
    padding-left: 0 !important;
    color: #1A1C22 !important;
    font-size: 0.84rem !important;
  }
  [data-ofs] .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
    top: 0 !important;
    right: 8px !important;
  }
  [data-ofs] .select2-dropdown {
    border: 1px solid #DDE0EA !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1) !important;
    overflow: hidden;
  }

  /* ── Item line card ── */
  .ofs-item-card {
    background: #FAFBFD;
    border: 1px solid #E8EAF0;
    border-radius: 10px;
    padding: 1rem 1.1rem;
    position: relative;
    transition: border-color 0.15s;
  }
  .ofs-item-card:hover { border-color: #C5CBE0; }
  .ofs-item-grid {
    display: grid;
    grid-template-columns: 2fr 3fr 80px 120px 38px;
    gap: 10px;
    align-items: end;
  }
  @media (max-width: 768px) {
    .ofs-item-grid {
      grid-template-columns: 1fr 1fr;
    }
    .ofs-item-grid > *:last-child { grid-column: span 2; }
  }

  /* ── Customization accordion ── */
  .ofs-custom-toggle {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    padding: 6px 10px;
    font-size: 0.76rem;
    font-weight: 500;
    color: #3B6ECC;
    background: #EEF4FF;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    width: fit-content;
  }
  .ofs-custom-toggle:hover { background: #DDE8FA; }
  .ofs-custom-panel {
    margin-top: 10px;
    padding: 12px;
    background: #fff;
    border: 1px solid #E8EAF0;
    border-radius: 8px;
  }
  .ofs-summary-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    color: #5A5F6E;
    background: #F0F2F7;
    border-radius: 4px;
    padding: 2px 7px;
    margin-top: 8px;
  }

  /* ── Summary panel ── */
  .ofs-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 0.84rem;
    color: #5A5F6E;
    border-bottom: 1px dashed #EEF0F7;
  }
  .ofs-summary-row:last-child { border-bottom: none; }
  .ofs-summary-row.total {
    font-size: 1rem;
    font-weight: 600;
    color: #0E0F11;
    border-top: 1.5px solid #E8EAF0;
    margin-top: 4px;
    padding-top: 12px;
  }
  .ofs-summary-value {
    font-family: 'DM Mono', monospace;
    font-weight: 500;
    font-size: 0.85rem;
  }
  .ofs-summary-row.total .ofs-summary-value {
    font-size: 1.05rem;
    color: #1D8A55;
  }
  .ofs-discount-value { color: #D94E4E; }

  /* ── Buttons ── */
  .ofs-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.84rem;
    font-weight: 500;
    border-radius: 9px;
    padding: 9px 18px;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.1s;
    text-decoration: none;
  }
  .ofs-btn:active { transform: scale(0.98); }
  .ofs-btn-primary {
    background: #1A1C22;
    color: #ffffff;
  }
  .ofs-btn-primary:hover { background: #2D3142; color: #fff; }
  .ofs-btn-outline {
    background: transparent;
    border: 1px solid #DDE0EA;
    color: #3B6ECC;
  }
  .ofs-btn-outline:hover { background: #EEF4FF; }
  .ofs-btn-ghost {
    background: transparent;
    border: 1px solid #DDE0EA;
    color: #8A8F9C;
  }
  .ofs-btn-ghost:hover { background: #F5F6F8; color: #1A1C22; }
  .ofs-btn-danger-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: #FFF0F0;
    border: 1px solid #F5C2C2;
    color: #D94E4E;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    flex-shrink: 0;
    transition: background 0.15s;
  }
  .ofs-btn-danger-icon:hover { background: #FFE0E0; }

  /* ── Add item ── */
  .ofs-add-item-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 11px;
    border: 1.5px dashed #C5CBE0;
    border-radius: 10px;
    background: transparent;
    color: #8A8F9C;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    margin-top: 10px;
  }
  .ofs-add-item-btn:hover {
    border-color: #3B6ECC;
    color: #3B6ECC;
    background: #EEF4FF;
  }

  /* ── Coupon hint ── */
  .ofs-coupon-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    font-weight: 500;
    color: #1D8A55;
    background: #EDFAF3;
    border: 1px solid #B4E8CF;
    border-radius: 6px;
    padding: 4px 10px;
    margin-top: 8px;
  }

  /* form-check inside our card */
  [data-ofs] .form-check { margin-bottom: 4px; }
  [data-ofs] .form-check-label { font-size: 0.80rem; color: #3A3D48; }
  [data-ofs] .form-check-input { margin-top: 2px; }

  /* ── footer action bar ── */
  .ofs-action-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 1.1rem 1.5rem;
    background: #fff;
    border: 1px solid #E8EAF0;
    border-radius: 14px;
    margin-top: 0.5rem;
  }
</style>
@endpush

@section('content')
<div data-ofs>
  @include('layouts.partials.messages')

  {{-- Page header --}}
  <div class="ofs-header">
    <div>
      <h1>{{ $page_title }}</h1>
      <p class="ofs-breadcrumb">Orders &rsaquo; View #{{ $order->id }}</p>
    </div>
  </div>

  <form method="POST" action="#" id="orderForm" autocomplete="off">
    @csrf

    {{-- ── Section 1: Order Details ── --}}
    <div class="ofs-card">
      <div class="ofs-card-header">
        <div class="ofs-card-header-left">
          <div class="ofs-card-icon blue">&#9783;</div>
          <div>
            <p class="ofs-card-title">Order Details</p>
            <p class="ofs-card-subtitle">Store, table & customer information</p>
          </div>
        </div>
      </div>
      <div class="ofs-card-body">
        <div class="ofs-field-grid">

          <div class="ofs-field">
            <label class="ofs-label">Store <span class="req">*</span></label>
            <select name="store_id" id="store_id" class="form-control select2" required>
              <option value="">Select store</option>
              @foreach($stores as $s)
                <option value="{{ $s->id }}" {{ (string)old('store_id', $order->store_id ?? '')===(string)$s->id?'selected':'' }}>{{ $s->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="ofs-field">
            <label class="ofs-label">Table # <span class="req">*</span></label>
            <input type="number" min="1" name="table_number" class="form-control" value="{{ old('table_number', $order->table_number ?? 1) }}" required>
          </div>

          <div class="ofs-field">
            <label class="ofs-label">Customer Phone <span class="req">*</span></label>
            <input name="customer_phone" class="form-control" value="{{ old('customer_phone', $order->customer->phone ?? '') }}" required>
          </div>

          <div class="ofs-field">
            <label class="ofs-label">Customer Email</label>
            <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $order->customer->email ?? '') }}">
          </div>

          <div class="ofs-field">
            <label class="ofs-label">Status <span class="req">*</span></label>
            <select name="status" class="form-control select2">
              @foreach($statuses as $st)
                <option value="{{ $st }}" {{ old('status', $order->status ?? 'received')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
              @endforeach
            </select>
          </div>

          <div class="ofs-field">
            <label class="ofs-label">Payment Method <span class="req">*</span></label>
            <select name="payment_method" class="form-control select2">
              <option value="cash"  {{ old('payment_method', $order->payment_method ?? 'cash')==='cash'?'selected':'' }}>Cash</option>
              <option value="card"  {{ old('payment_method', $order->payment_method ?? 'cash')==='card'?'selected':'' }}>Card</option>
              <option value="upi"   {{ old('payment_method', $order->payment_method ?? 'cash')==='upi'?'selected':'' }}>UPI</option>
            </select>
          </div>

          <div class="ofs-field">
            <label class="ofs-label">Coupon</label>
            <select name="promotion_id" id="promotion_id" class="form-control select2">
              <option value="">No coupon</option>
              @foreach($promotions as $pr)
                <option value="{{ $pr->id }}"
                  data-code="{{ $pr->code }}"
                  data-type="{{ $pr->type }}"
                  data-value="{{ $pr->discount_value }}"
                  data-max="{{ $pr->max_discount_amount }}"
                  data-min="{{ $pr->min_cart_amount }}"
                  {{ (string)old('promotion_id', $order->promotion_id ?? '')===(string)$pr->id?'selected':'' }}>
                  {{ $pr->name }} ({{ $pr->code }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="ofs-field">
            <label class="ofs-label">Coupon Code</label>
            <input name="coupon_code" id="coupon_code" class="form-control" value="{{ old('coupon_code', $order->coupon_code ?? '') }}">
          </div>

        </div>
      </div>
    </div>

    {{-- ── Section 2: Order Items ── --}}
    <div class="ofs-card">
      <div class="ofs-card-header">
        <div class="ofs-card-header-left">
          <div class="ofs-card-icon green">&#9776;</div>
          <div>
            <p class="ofs-card-title">Order Items</p>
            <p class="ofs-card-subtitle">Products, quantities & customizations</p>
          </div>
        </div>
      </div>
      <div class="ofs-card-body">
        <div id="itemsContainer" style="display:flex; flex-direction:column; gap:10px;"></div>

      </div>
    </div>

    {{-- ── Section 3: Order Summary ── --}}
    <div class="ofs-card">
      <div class="ofs-card-header">
        <div class="ofs-card-header-left">
          <div class="ofs-card-icon amber">&#36;</div>
          <div>
            <p class="ofs-card-title">Order Summary</p>
            <p class="ofs-card-subtitle">Totals & applied discounts</p>
          </div>
        </div>
      </div>
      <div class="ofs-card-body" style="max-width:400px; margin-left:auto;">
        <div class="ofs-summary-row">
          <span>Subtotal</span>
          <span class="ofs-summary-value" id="sub_total_text">0.00</span>
        </div>
        <div class="ofs-summary-row">
          <span>Discount</span>
          <span class="ofs-summary-value ofs-discount-value" id="discount_text">0.00</span>
        </div>
        <div class="ofs-summary-row total">
          <span>Grand Total</span>
          <span class="ofs-summary-value" id="grand_total_text">0.00</span>
        </div>
        <div id="coupon_hint"></div>
      </div>
    </div>

    {{-- ── Action bar ── --}}
    <div class="ofs-action-bar">
      @can('menu_orders.edit')<a href="{{ route('menu.orders.edit', $order->id) }}" class="ofs-btn btn-primary">Edit Order</a>@endcan
      <a href="{{ route('menu.orders.index') }}" class="ofs-btn ofs-btn-ghost">&larr; Back to Orders</a>
    </div>

  </form>
</div>

<script type="application/json" id="menu-data-json">@json($initialMenuData)</script>
<script type="application/json" id="existing-items-json">@json($existingItems ?? [])</script>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
/* ══════════════════════════════════════════════
   ORDER FORM — All original logic preserved
══════════════════════════════════════════════ */
let menuData = JSON.parse(document.getElementById('menu-data-json').textContent || '{"categories":[],"products":[]}');
const existingItems = JSON.parse(document.getElementById('existing-items-json').textContent || '[]');

function getProductsByCategory(cid) { return (menuData.products||[]).filter(p => String(p.category_id) === String(cid)); }
function findProduct(id) { return (menuData.products||[]).find(p => String(p.id) === String(id)); }

function categoryOptions(selectedId) {
  let html='<option value="">Select category</option>';
  (menuData.categories||[]).forEach(c=>{ const s=String(selectedId||'')===String(c.id)?'selected':''; html+='<option value="'+c.id+'" '+s+'>'+c.name+'</option>'; });
  return html;
}
function productOptions(categoryId, selectedId) {
  let html='<option value="">Select product</option>';
  getProductsByCategory(categoryId).forEach(p=>{ const s=String(selectedId||'')===String(p.id)?'selected':''; html+='<option value="'+p.id+'" '+s+'>'+p.name+'</option>'; });
  return html;
}

function lineCard(idx, row) {
  const cid = row.category_id || '';
  return `
  <div class="ofs-item-card item-card" data-idx="${idx}">
    <div class="ofs-item-grid">
      <div class="ofs-field">
        <label class="ofs-label">Category</label>
        <select class="form-control item-category" name="items[${idx}][category_id]">${categoryOptions(cid)}</select>
      </div>
      <div class="ofs-field">
        <label class="ofs-label">Product <span class="req" style="color:#D94E4E">*</span></label>
        <select class="form-control item-product" name="items[${idx}][product_id]" required>${productOptions(cid, row.product_id)}</select>
      </div>
      <div class="ofs-field">
        <label class="ofs-label">Qty</label>
        <input type="number" min="1" class="form-control item-qty" name="items[${idx}][quantity]" value="${row.quantity||1}">
      </div>
      <div class="ofs-field">
        <label class="ofs-label">Unit Price</label>
        <input type="number" step="0.01" min="0" class="form-control item-price" name="items[${idx}][unit_price]" value="${row.unit_price||0}">
      </div>
      <div class="ofs-field" style="display:flex;align-items:flex-end;">
        <button type="button" class="ofs-btn-danger-icon remove-item d-none" title="Remove item">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 13 6"/><path d="M6 6V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"/><path d="M5 6l.8 8h4.4l.8-8"/></svg>
        </button>
      </div>
    </div>

    <details class="ofs-custom-details" style="margin-top:10px;">
      <summary class="ofs-custom-toggle" style="list-style:none;display:inline-flex;align-items:center;gap:6px;padding:5px 10px;font-size:0.76rem;font-weight:500;color:#3B6ECC;background:#EEF4FF;border-radius:6px;cursor:pointer;border:none;user-select:none;">
        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="6"/><line x1="8" y1="5" x2="8" y2="11"/><line x1="5" y1="8" x2="11" y2="8"/></svg>
        Manage Attributes &amp; Add-ons
      </summary>
      <div class="ofs-custom-panel customization-wrap" style="margin-top:8px;"></div>
    </details>

    <div class="selected-summary ofs-summary-chip">No customization selected.</div>
    <input type="hidden" class="item-attrs" name="items[${idx}][attributes_json]" value="${row.attributes_json||''}">
    <input type="hidden" class="item-addons" name="items[${idx}][addons_json]" value="${row.addons_json||''}">
  </div>`;
}

function rebuildNames() {
  $('#itemsContainer .item-card').each(function(i){
    $(this).attr('data-idx', i);
    $(this).find('[name]').each(function(){
      const n=$(this).attr('name'); if(!n) return;
      $(this).attr('name', n.replace(/items\[\d+\]/,'items['+i+']'));
    });
  });
}

function renderCustomization(card) {
  const pid = card.find('.item-product').val();
  const p = findProduct(pid);
  const wrap = card.find('.customization-wrap');
  if(!p){ wrap.html('<div style="font-size:0.78rem;color:#8A8F9C;padding:4px 0;">Select a product to see customization options.</div>'); return; }

  let html = '';
  (p.attributes||[]).forEach((g,gi)=>{
    html += '<div style="margin-bottom:10px;"><div style="font-size:0.75rem;font-weight:600;color:#1A1C22;margin-bottom:5px;">'+(g.attribute_name||'Attribute')+'</div>';
    (g.values||[]).forEach((v,vi)=>{
      const checked = v.is_default ? 'checked' : '';
      html += '<label class="form-check form-check-sm" style="margin-bottom:3px;"><input class="form-check-input attr-choice" type="radio" name="attr_'+card.data('idx')+'_'+gi+'" data-attr="'+(g.attribute_name||'')+'" data-val="'+(v.value||'')+'" data-extra="'+(v.extra_price||0)+'" '+checked+'> <span class="form-check-label">'+(v.value||'')+' <span style="color:#8A8F9C;font-size:0.72rem;">(+'+parseFloat(v.extra_price||0).toFixed(2)+')</span></span></label>';
    });
    html += '</div>';
  });
  if((p.addons||[]).length) {
    html += '<div><div style="font-size:0.75rem;font-weight:600;color:#1A1C22;margin-bottom:5px;">Add-ons</div>';
    (p.addons||[]).forEach(a=>{
      const checked = a.is_default ? 'checked' : '';
      html += '<label class="form-check form-check-sm" style="margin-bottom:3px;"><input class="form-check-input addon-choice" type="checkbox" data-name="'+(a.name||'')+'" data-extra="'+(a.extra_price||0)+'" '+checked+'> <span class="form-check-label">'+(a.name||'')+' <span style="color:#8A8F9C;font-size:0.72rem;">(+'+parseFloat(a.extra_price||0).toFixed(2)+')</span></span></label>';
    });
    html += '</div>';
  }
  if(!html) html='<div style="font-size:0.78rem;color:#8A8F9C;">No customization available for this product.</div>';
  wrap.html(html);
  syncCustomToHidden(card, false);
}

function syncCustomToHidden(card, adjustPrice=true) {
  const attrs=[]; const addons=[]; let extra=0;
  card.find('.attr-choice:checked').each(function(){ const e=parseFloat($(this).data('extra')||0); extra+=e; attrs.push({attribute_name:$(this).data('attr'), value:$(this).data('val'), extra_price:e}); });
  card.find('.addon-choice:checked').each(function(){ const e=parseFloat($(this).data('extra')||0); extra+=e; addons.push({name:$(this).data('name'), extra_price:e}); });
  card.find('.item-attrs').val(JSON.stringify(attrs));
  card.find('.item-addons').val(JSON.stringify(addons));
  const chips=[...attrs.map(a=>a.attribute_name+': '+a.value), ...addons.map(a=>'Add-on: '+a.name)];
  card.find('.selected-summary').text(chips.length?chips.join(' | '):'No customization selected.');
  if(adjustPrice){ const base=parseFloat(findProduct(card.find('.item-product').val())?.base_price||0); card.find('.item-price').val((base+extra).toFixed(2)); }
  recalcTotals();
}

function recalcTotals() {
  let sub=0;
  $('#itemsContainer .item-card').each(function(){
    const q=parseFloat($(this).find('.item-qty').val())||0;
    const p=parseFloat($(this).find('.item-price').val())||0;
    sub += q*p;
  });
  const promo=$('#promotion_id option:selected');
  const type=promo.data('type');
  const value=parseFloat(promo.data('value')||0);
  const max=parseFloat(promo.data('max')||0);
  const min=parseFloat(promo.data('min')||0);
  let discount=0;
  if(type && (!min || sub>=min)){
    if(type==='cart_flat') discount=Math.min(sub, value);
    if(type==='cart_percent'){ discount=sub*(value/100); if(max) discount=Math.min(discount,max); }
  }
  const grand=Math.max(0, sub-discount);
  $('#sub_total_text').text(sub.toFixed(2));
  $('#discount_text').text(discount.toFixed(2));
  $('#grand_total_text').text(grand.toFixed(2));

  const hint = document.getElementById('coupon_hint');
  if(type && promo.data('code')){
    hint.innerHTML = '<span class="ofs-coupon-badge">&#10003; Coupon <strong>'+promo.data('code')+'</strong> applied &mdash; saving '+discount.toFixed(2)+'</span>';
  } else {
    hint.innerHTML = '';
  }
}

function renderAll(rows) {
  const data=(rows&&rows.length)?rows:[{quantity:1,unit_price:0,addons_json:'',attributes_json:''}];
  $('#itemsContainer').empty();
  data.forEach((r,i)=>$('#itemsContainer').append(lineCard(i,r)));
  $('#itemsContainer .item-category, #itemsContainer .item-product').select2({width:'100%'});
  $('#itemsContainer .item-card').each(function(){ renderCustomization($(this)); });
  recalcTotals();
}

$(function(){
  $('select[name="store_id"], select[name="status"], select[name="payment_method"], #promotion_id').select2({width:'100%'});
  renderAll(existingItems);

  $('#store_id').on('change', function(){
    const id=$(this).val();
    if(!id){ menuData={categories:[],products:[]}; renderAll([]); return; }
    $.get("{{ route('menu.orders.store-menu-data') }}", {store_id:id}, function(res){ menuData=res||{categories:[],products:[]}; renderAll([]); });
  });

  $('#addItemCard').on('click', function(){
    if(!$('#store_id').val()){ Swal.fire('Select a store first','Please select a store before adding items.','warning'); return; }
    const idx=$('#itemsContainer .item-card').length;
    $('#itemsContainer').append(lineCard(idx,{quantity:1,unit_price:0,addons_json:'',attributes_json:''}));
    $('#itemsContainer .item-card:last .item-category, #itemsContainer .item-card:last .item-product').select2({width:'100%'});
  });

  $(document).on('change','.item-category', function(){
    const card=$(this).closest('.item-card');
    const cid=$(this).val();
    const productSelect=card.find('.item-product');
    productSelect.html(productOptions(cid,''));
    productSelect.trigger('change');
  });

  $(document).on('change','.item-product', function(){
    const card=$(this).closest('.item-card');
    const p=findProduct($(this).val());
    if(p){ card.find('.item-price').val(parseFloat(p.base_price||0).toFixed(2)); }
    renderCustomization(card);
  });

  $(document).on('change','.attr-choice,.addon-choice', function(){
    syncCustomToHidden($(this).closest('.item-card'));
  });

  $(document).on('click','.remove-item', function(){
    $(this).closest('.item-card').remove();
    if(!$('#itemsContainer .item-card').length) renderAll([]);
    rebuildNames();
    recalcTotals();
  });

  $(document).on('input change','.item-qty,.item-price,#promotion_id', recalcTotals);
  $('#promotion_id').on('change', function(){ const code=$('#promotion_id option:selected').data('code')||''; $('#coupon_code').val(code); recalcTotals(); });

  $('#orderForm').on('submit', function(){ rebuildNames(); });

  if($('#store_id').val() && (!menuData.categories || !menuData.categories.length)){
    $('#store_id').trigger('change');
  } else {
    recalcTotals();
  }
  // view-only mode
  $('#orderForm').find('input, select, textarea, button').prop('disabled', true);
  $('#orderForm').find('a').prop('disabled', false);
  $('#promotion_id').prop('disabled', true);

});
</script>
@endpush