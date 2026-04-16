<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $store->name }} - Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f7f7f7; padding-bottom: 90px; }
        .hero { background: linear-gradient(135deg,#065e2e); color:#fff; border-radius: 0 0 18px 18px; }
        .product-card { border:0; border-radius:14px; box-shadow:0 6px 18px rgba(0,0,0,.06); }
        .product-img { width:88px; height:88px; object-fit:cover; border-radius:12px; background:#eee; }
        .cart-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #101828; color:#fff; z-index: 1060; }
    </style>
</head>
<body>
<div class="hero p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div><h5 class="mb-0">{{ $store->name }}</h5><small>Table {{ $tableNo }}</small></div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-light" href="{{ route('ordering.my-orders') }}">My Orders</a>
            <a class="btn btn-sm btn-light" href="{{ route('ordering.logout') }}">Change Table</a>
        </div>
    </div>
</div>

<div class="container-fluid px-2 px-md-3">
    @foreach($categories as $cat)
        @php $catProducts = $products->where('category_id', $cat->id); @endphp
        @if($catProducts->count())
            <h6 class="px-1 mb-2">{{ $cat->name }}</h6>
            @foreach($catProducts as $p)
            <div class="card product-card mb-2">
                <div class="card-body py-2">
                    <div class="d-flex gap-2">
                        <img src="{{ $p->image_url ?: 'https://via.placeholder.com/120x120?text=Food' }}" class="product-img" alt="">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $p->name }}</div>
                            <div class="small text-muted">{{ $p->description }}</div>
                            <div class="small mt-1">Rs {{ number_format((float)$p->base_price,2) }}</div>
                            <div class="mt-2 product-actions" data-product-id="{{ $p->id }}">
                                <button type="button" class="btn btn-sm btn-danger product-add-btn" data-product-id="{{ $p->id }}">Add</button>
                                <div class="d-none align-items-center gap-2 product-stepper-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary product-minus" data-product-id="{{ $p->id }}">-</button>
                                    <span class="small fw-semibold product-qty" id="qty-{{ $p->id }}">0</span>
                                    <button type="button" class="btn btn-sm btn-danger product-plus" data-product-id="{{ $p->id }}">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    @endforeach
</div>

<div class="offcanvas offcanvas-bottom" tabindex="-1" id="cartCanvas" style="height:78vh;">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Your Cart</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
  <div class="offcanvas-body">
    <div id="cartItems" class="small text-muted mb-2">Cart is empty</div>
    <div class="input-group input-group-sm mb-2"><input id="coupon_code" class="form-control" placeholder="Coupon code"><button id="applyCoupon" class="btn btn-outline-success">Apply</button></div>
    <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="subTotal">0.00</strong></div>
    <div class="d-flex justify-content-between"><span>Discount</span><strong id="discountTotal">0.00</strong></div>
    <hr class="my-2">
    <div class="d-flex justify-content-between"><span>Total</span><strong id="grandTotal">0.00</strong></div>
    <button id="placeOrder" class="btn btn-success w-100 mt-3">Cash Payment & Place Order</button>
    <div class="small text-success mt-2" id="orderMsg"></div>
  </div>
</div>

<div class="modal fade" id="customizeModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="customizeTitle">Customize</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div id="customizeBody"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" id="confirmCustomize">Add to Cart</button>
      </div>
    </div>
  </div>
</div>

<button class="cart-bar btn w-100 py-3 rounded-0" data-bs-toggle="offcanvas" data-bs-target="#cartCanvas">
    <span id="cartBarText">View Cart • 0 items • Rs 0.00</span>
</button>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="application/json" id="products-json">{!! json_encode($productsPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script>
const cart={items:{}, coupon:'', discount:0};
let pendingProduct=null;
const productsMap = JSON.parse(document.getElementById('products-json').textContent || '{}');
const customizeModal = new bootstrap.Modal(document.getElementById('customizeModal'));

function itemKey(baseId, attrs, addons){
    const a = encodeURIComponent(JSON.stringify(attrs||[]));
    const d = encodeURIComponent(JSON.stringify(addons||[]));
    return String(baseId) + '|' + a + '|' + d;
}

function calcItemPrice(base, attrs, addons){
    let price = parseFloat(base||0);
    (attrs||[]).forEach(a => { price += parseFloat(a.extra_price||0); });
    (addons||[]).forEach(a => { price += parseFloat(a.extra_price||0); });
    return price;
}

function renderCart(){
  const rows=[]; let sub=0, count=0;
  Object.values(cart.items).forEach(i=>{
    const line=i.qty*i.price; sub+=line; count+=i.qty;
    const chips = []
      .concat((i.attributes||[]).map(v=>v.attribute_name+': '+v.value))
      .concat((i.addons||[]).map(v=>'Add-on: '+v.name));
    rows.push('<div class="border rounded p-2 mb-2"><div class="d-flex justify-content-between"><div><div class="fw-semibold">'+i.name+'</div><div class="small text-muted">'+chips.join(' • ')+'</div></div><div>Rs '+line.toFixed(2)+'</div></div><div class="mt-1"><button class="btn btn-sm btn-light dec" data-id="'+i.key+'">-</button> <span class="mx-1">'+i.qty+'</span> <button class="btn btn-sm btn-light inc" data-id="'+i.key+'">+</button></div></div>');
  });
  $('#cartItems').html(rows.length?rows.join(''):'Cart is empty');
  $('#subTotal').text(sub.toFixed(2));
  $('#discountTotal').text((cart.discount||0).toFixed(2));
  const grand = Math.max(0, sub-(cart.discount||0));
  $('#grandTotal').text(grand.toFixed(2));
  $('#cartBarText').text('View Cart • '+count+' items • Rs '+grand.toFixed(2));
  syncProductSteppers();
}

function getProductQty(productId){
    let qty = 0;
    Object.values(cart.items).forEach(i => { if (String(i.id) === String(productId)) qty += i.qty; });
    return qty;
}

function syncProductSteppers(){
    $('.product-actions').each(function(){
        const pid = String($(this).data('product-id'));
        const qty = getProductQty(pid);
        $(this).find('.product-qty').text(qty);
        $(this).find('.product-minus').prop('disabled', qty <= 0);
        if (qty > 0) {
            $(this).find('.product-add-btn').addClass('d-none');
            $(this).find('.product-stepper-wrap').removeClass('d-none').addClass('d-flex');
        } else {
            $(this).find('.product-stepper-wrap').removeClass('d-flex').addClass('d-none');
            $(this).find('.product-add-btn').removeClass('d-none');
        }
    });
}

function openCustomize(product){
    pendingProduct = product;
    $('#customizeTitle').text('Customize '+product.name);
    let html = '';
    (product.attributes||[]).forEach(group=>{
        html += '<div class="mb-3"><div class="fw-semibold mb-1">'+group.attribute_name+'</div>';
        (group.values||[]).forEach(v=>{
            const checked = v.is_default ? 'checked' : '';
            html += '<label class="form-check mb-1"><input class="form-check-input attr-opt" type="radio" name="attr_'+group.attribute_id+'" data-attr-name="'+group.attribute_name+'" data-val="'+v.value+'" data-extra="'+(v.extra_price||0)+'" '+checked+'> <span class="form-check-label">'+v.value+' <small class="text-muted">(+Rs '+parseFloat(v.extra_price||0).toFixed(2)+')</small></span></label>';
        });
        html += '</div>';
    });
    if((product.addons||[]).length){
        html += '<div class="mb-2"><div class="fw-semibold mb-1">Add-ons</div>';
        (product.addons||[]).forEach(a=>{
            const checked = a.is_default ? 'checked' : '';
            html += '<label class="form-check mb-1"><input class="form-check-input addon-opt" type="checkbox" data-name="'+a.name+'" data-extra="'+(a.extra_price||0)+'" '+checked+'> <span class="form-check-label">'+a.name+' <small class="text-muted">(+Rs '+parseFloat(a.extra_price||0).toFixed(2)+')</small></span></label>';
        });
        html += '</div>';
    }
    if (!html) html = '<div class="text-muted">No customizations for this item.</div>';
    $('#customizeBody').html(html);
    customizeModal.show();
}

function addProductFlow(pid){
    const product = productsMap[pid];
    if(!product) return;
    if((product.attributes||[]).length || (product.addons||[]).length){ openCustomize(product); return; }
    const key = itemKey(product.id, [], []);
    if(!cart.items[key]) cart.items[key]={key:key,id:product.id,name:product.name,price:parseFloat(product.price),qty:0,attributes:[],addons:[]};
    cart.items[key].qty++; renderCart();
}

$(document).on('click','.product-plus',function(){
    const pid = String($(this).data('product-id'));
    addProductFlow(pid);
});

$(document).on('click','.product-add-btn',function(){
    const pid = String($(this).data('product-id'));
    addProductFlow(pid);
});

$(document).on('click','.product-minus',function(){
    const pid = String($(this).data('product-id'));
    const keys = Object.keys(cart.items).filter(k => String(cart.items[k].id) === pid);
    if(!keys.length) return;
    const key = keys.sort((a,b) => cart.items[b].qty - cart.items[a].qty)[0];
    cart.items[key].qty--;
    if(cart.items[key].qty <= 0) delete cart.items[key];
    renderCart();
});

$('#confirmCustomize').on('click', function(){
    if(!pendingProduct) return;
    const attrs=[]; const addons=[];
    $('#customizeBody .attr-opt:checked').each(function(){
        attrs.push({attribute_name:$(this).data('attr-name'), value:$(this).data('val'), extra_price:parseFloat($(this).data('extra')||0)});
    });
    $('#customizeBody .addon-opt:checked').each(function(){
        addons.push({name:$(this).data('name'), extra_price:parseFloat($(this).data('extra')||0)});
    });
    const itemPrice = calcItemPrice(pendingProduct.price, attrs, addons);
    const key = itemKey(pendingProduct.id, attrs, addons);
    if(!cart.items[key]) cart.items[key]={key:key,id:pendingProduct.id,name:pendingProduct.name,price:itemPrice,qty:0,attributes:attrs,addons:addons};
    cart.items[key].qty++;
    customizeModal.hide();
    renderCart();
});

$(document).on('click','.inc',function(){ const id=$(this).data('id'); if(cart.items[id]) cart.items[id].qty++; renderCart(); });
$(document).on('click','.dec',function(){ const id=$(this).data('id'); if(cart.items[id]){ cart.items[id].qty--; if(cart.items[id].qty<=0) delete cart.items[id]; } renderCart(); });

$('#applyCoupon').on('click', function(){
  const items=Object.values(cart.items).map(i=>({product_id:i.id,category_id:null,qty:i.qty,price:i.price}));
  const sub=parseFloat($('#subTotal').text())||0;
  $.post("{{ route('ordering.coupon.apply') }}",{_token:$('meta[name="csrf-token"]').attr('content'), coupon_code:$('#coupon_code').val(), cart_total:sub, items:items})
    .done(function(res){ cart.discount=parseFloat(res.discount||0); cart.coupon=res.code||$('#coupon_code').val(); renderCart(); Swal.fire('Applied', (res.code?('Coupon '+res.code+' applied'): 'Best offer applied'), 'success'); })
    .fail(function(x){ cart.discount=0; renderCart(); Swal.fire('Coupon', x.responseJSON?.message || 'Not applicable', 'warning'); });
});

$('#placeOrder').on('click', function(){
  const items=Object.values(cart.items).map(i=>({product_id:i.id,name:i.name,qty:i.qty,price:i.price,attributes:i.attributes,addons:i.addons}));
  if(!items.length){ Swal.fire('Cart empty','Add items first','warning'); return; }
  $.post("{{ route('ordering.place-order') }}",{_token:$('meta[name="csrf-token"]').attr('content'), items:items, coupon_code:cart.coupon, discount_total:parseFloat($('#discountTotal').text())||0, grand_total:parseFloat($('#grandTotal').text())||0})
    .done(function(res){ $('#orderMsg').text(res.message+' ('+res.order_number+')'); cart.items={}; cart.coupon=''; cart.discount=0; $('#coupon_code').val(''); renderCart(); })
    .fail(function(x){ Swal.fire('Error', x.responseJSON?.message || 'Unable to place order', 'error'); });
});

renderCart();
</script>
</body>
</html>
