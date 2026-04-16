<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreOrderRequest;
use App\Http\Requests\Menu\UpdateOrderRequest;
use App\Models\MenuOrder;
use App\Models\MenuOrderItem;
use App\Models\MenuProduct;
use App\Models\OrderingUser;
use App\Models\Promotion;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu_orders.view')->only(['index', 'show']);
        $this->middleware('permission:menu_orders.create')->only(['create', 'store']);
        $this->middleware('permission:menu_orders.edit')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('permission:menu_orders.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MenuOrder::query()->with(['store', 'customer'])->orderByDesc('id');

            if ($request->filled('store_id')) {
                $query->where('store_id', (int) $request->input('store_id'));
            }
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->input('payment_method'));
            }
            if ($request->filled('order_number')) {
                $query->where('order_number', 'like', '%' . trim((string) $request->input('order_number')) . '%');
            }
            if ($request->filled('table_number')) {
                $query->where('table_number', (int) $request->input('table_number'));
            }
            if ($request->filled('customer_phone')) {
                $phone = trim((string) $request->input('customer_phone'));
                $query->whereHas('customer', function ($q) use ($phone) {
                    $q->where('phone', 'like', '%' . $phone . '%');
                });
            }
            if ($request->filled('min_total')) {
                $query->where('grand_total', '>=', (float) $request->input('min_total'));
            }
            if ($request->filled('max_total')) {
                $query->where('grand_total', '<=', (float) $request->input('max_total'));
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            return datatables()->eloquent($query)
                ->addColumn('store_name', fn($r) => optional($r->store)->name)
                ->addColumn('customer_phone', fn($r) => optional($r->customer)->phone)
                ->addColumn('status_badge', function ($r) {
                    $map = ['received'=>'secondary','confirmed'=>'info','preparing'=>'warning','ready'=>'primary','served'=>'success','cancelled'=>'danger'];
                    $c = $map[$r->status] ?? 'secondary';
                    return '<span class="badge bg-'.$c.'">'.e(ucfirst($r->status)).'</span>';
                })
                ->addColumn('status_control', function ($r) {
                    if (! auth()->user()->can('menu_orders.edit')) {
                        return '<span class="text-muted">—</span>';
                    }
                    $html = '<select class="form-control form-control-sm order-status-select" data-id="' . $r->id . '" style="min-width:130px;">';
                    foreach (MenuOrder::STATUSES as $status) {
                        $selected = $r->status === $status ? ' selected' : '';
                        $html .= '<option value="' . e($status) . '"' . $selected . '>' . e(ucfirst($status)) . '</option>';
                    }
                    $html .= '</select>';
                    return $html;
                })
                ->addColumn('action', function ($r) {
                    $h='';
                    if (auth()->user()->can('menu_orders.view')) $h.='<a href="'.route('menu.orders.show',$r->id).'" class="btn btn-sm btn-secondary me-1"><i class="bi bi-eye"></i></a>';
                    if (auth()->user()->can('menu_orders.edit')) $h.='<a href="'.route('menu.orders.edit',$r->id).'" class="btn btn-sm btn-info me-1"><i class="bi bi-pencil"></i></a>';
                    if (auth()->user()->can('menu_orders.delete')) $h.='<button type="button" class="btn btn-sm btn-danger btn-delete-order" data-id="'.$r->id.'"><i class="bi bi-trash"></i></button>';
                    return $h;
                })
                ->editColumn('created_at', function ($r) {
                    return date('Y-m-d H:i:s', strtotime($r->created_at));
                })
                ->rawColumns(['status_badge', 'status_control', 'action'])
                ->toJson();
        }

        $page_title = 'Order Management';
        $stores = Store::loc()->orderBy('name')->get(['id', 'name']);
        $statuses = MenuOrder::STATUSES;
        $paymentMethods = ['cash', 'card', 'upi'];
        return view('menu.orders.index', compact('page_title', 'stores', 'statuses', 'paymentMethods'));
    }

    public function create()
    {
        $page_title = 'Create Order';
        $order = new MenuOrder();
        $stores = Store::loc()->orderBy('name')->get(['id','name']);
        $promotions = Promotion::where('is_active', 1)->orderByDesc('priority')->orderBy('name')->get(['id', 'name', 'code', 'type', 'discount_value', 'max_discount_amount', 'min_cart_amount']);
        $statuses = MenuOrder::STATUSES;
        $existingItems = [];
        $initialMenuData = ['categories' => [], 'products' => []];
        return view('menu.orders.create', compact('page_title', 'order', 'stores', 'promotions', 'statuses', 'existingItems', 'initialMenuData'));
    }

    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $customer = $this->upsertCustomer($request->input('customer_phone'), $request->input('customer_email'));
            return $this->saveOrder($request, new MenuOrder(), $customer->id);
        });

        return redirect()->route('menu.orders.show', $order->id)->with('success', 'Order created successfully.');
    }

    public function show(MenuOrder $order)
    {
        $order->load(['store', 'customer', 'items.product']);
        $page_title = 'Order #' . $order->order_number;
        $stores = Store::loc()->orderBy('name')->get(['id', 'name']);
        $promotions = Promotion::where('is_active', 1)
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'discount_value', 'max_discount_amount', 'min_cart_amount']);
        $statuses = MenuOrder::STATUSES;
        $existingItems = $order->items->map(function ($it) {
            return [
                'product_id' => $it->product_id,
                'category_id' => optional($it->product)->category_id,
                'quantity' => $it->quantity,
                'unit_price' => (float) $it->unit_price,
                'addons' => $it->addons ?: [],
                'attributes' => $it->attributes ?: [],
            ];
        })->values();
        $initialMenuData = $this->buildStoreMenuData((int) $order->store_id);

        return view('menu.orders.show', compact(
            'page_title',
            'order',
            'stores',
            'promotions',
            'statuses',
            'existingItems',
            'initialMenuData'
        ));
    }

    public function edit(MenuOrder $order)
    {
        $page_title = 'Edit Order #' . $order->order_number;
        $stores = Store::loc()->orderBy('name')->get(['id','name']);
        $promotions = Promotion::where('is_active', 1)->orderByDesc('priority')->orderBy('name')->get(['id', 'name', 'code', 'type', 'discount_value', 'max_discount_amount', 'min_cart_amount']);
        $statuses = MenuOrder::STATUSES;
        $order->load(['items', 'customer']);
        $existingItems = $order->items->map(function ($it) {
            return [
                'product_id' => $it->product_id,
                'category_id' => optional($it->product)->category_id,
                'quantity' => $it->quantity,
                'unit_price' => (float) $it->unit_price,
                'addons' => $it->addons ?: [],
                'attributes' => $it->attributes ?: [],
            ];
        })->values();
        $initialMenuData = $this->buildStoreMenuData((int) $order->store_id);
        return view('menu.orders.edit', compact('page_title', 'order', 'stores', 'promotions', 'statuses', 'existingItems', 'initialMenuData'));
    }

    public function storeMenuData(Request $request)
    {
        $request->validate(['store_id' => 'required|exists:stores,id']);
        return response()->json($this->buildStoreMenuData((int) $request->input('store_id')));
    }

    public function update(UpdateOrderRequest $request, MenuOrder $order)
    {
        DB::transaction(function () use ($request, $order) {
            $customer = $this->upsertCustomer($request->input('customer_phone'), $request->input('customer_email'));
            $this->saveOrder($request, $order, $customer->id);
        });

        return redirect()->route('menu.orders.show', $order->id)->with('success', 'Order updated successfully.');
    }

    public function updateStatus(Request $request, MenuOrder $order)
    {
        $request->validate(['status' => 'required|in:' . implode(',', MenuOrder::STATUSES)]);
        $order->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, MenuOrder $order)
    {
        DB::transaction(function () use ($order) {
            MenuOrderItem::where('menu_order_id', $order->id)->delete();
            $order->delete();
        });
        if ($request->ajax()) return response()->json(['success' => true]);
        return redirect()->route('menu.orders.index')->with('success', 'Order deleted.');
    }

    protected function upsertCustomer(string $phone, ?string $email): OrderingUser
    {
        $customer = OrderingUser::firstOrCreate(['phone' => $phone], ['email' => $email]);
        if ($email && $customer->email !== $email) {
            $customer->update(['email' => $email]);
        }
        return $customer;
    }

    protected function saveOrder(Request $request, MenuOrder $order, int $customerId): MenuOrder
    {
        $items = collect($request->input('items', []))->map(function ($row) {
            $qty = (int) $row['quantity'];
            $price = (float) $row['unit_price'];
            return [
                'product_id' => (int) $row['product_id'],
                'quantity' => $qty,
                'unit_price' => $price,
                'line_total' => $qty * $price,
                'addons' => $this->decodeJson($row['addons_json'] ?? null),
                'attributes' => $this->decodeJson($row['attributes_json'] ?? null),
            ];
        });

        $subTotal = (float) $items->sum('line_total');
        $promotion = null;
        if ($request->filled('promotion_id')) {
            $promotion = Promotion::find((int) $request->input('promotion_id'));
        } elseif ($request->filled('coupon_code')) {
            $promotion = Promotion::whereRaw('UPPER(code)=?', [strtoupper((string) $request->input('coupon_code'))])->first();
        }
        $discountTotal = $this->calculateDiscount($promotion, $items, $subTotal);
        $grandTotal = max(0, $subTotal - $discountTotal);

        $order->fill([
            'store_id' => (int) $request->input('store_id'),
            'ordering_user_id' => $customerId,
            'table_number' => (int) $request->input('table_number'),
            'status' => $request->input('status'),
            'payment_method' => $request->input('payment_method'),
            'coupon_code' => $promotion ? $promotion->code : $request->input('coupon_code'),
            'promotion_id' => optional($promotion)->id,
            'sub_total' => $subTotal,
            'discount_total' => $discountTotal,
            'grand_total' => $grandTotal,
            'meta' => ['source' => 'admin'],
        ]);

        if (! $order->exists) {
            $order->order_number = 'ADM' . now()->format('ymdHis') . random_int(10, 99);
        }

        $order->save();

        MenuOrderItem::where('menu_order_id', $order->id)->delete();
        foreach ($items as $item) {
            $product = MenuProduct::find($item['product_id']);
            MenuOrderItem::create([
                'menu_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => optional($product)->name ?: 'Product #' . $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
                'addons' => $item['addons'],
                'attributes' => $item['attributes'],
            ]);
        }

        return $order;
    }

    protected function decodeJson(?string $json): array
    {
        if (! $json) return [];
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function calculateDiscount(?Promotion $promotion, $items, float $subTotal): float
    {
        if (! $promotion) return 0.0;
        if ($promotion->min_cart_amount && $subTotal < (float) $promotion->min_cart_amount) return 0.0;

        $type = $promotion->type;
        if ($type === 'cart_flat') {
            return min($subTotal, (float) ($promotion->discount_value ?? 0));
        }
        if ($type === 'cart_percent') {
            $d = $subTotal * ((float) ($promotion->discount_value ?? 0) / 100);
            return $promotion->max_discount_amount ? min($d, (float) $promotion->max_discount_amount) : $d;
        }

        $productMap = MenuProduct::whereIn('id', $items->pluck('product_id')->all())->pluck('category_id', 'id');
        if ($type === 'product_flat' || $type === 'product_percent') {
            $allow = collect($promotion->applicable_product_ids ?? [])->map(fn($v) => (int) $v)->all();
            $sub = $items->filter(fn($i) => in_array((int) $i['product_id'], $allow, true))->sum('line_total');
            if ($type === 'product_flat') return min($sub, (float) ($promotion->discount_value ?? 0));
            $d = $sub * ((float) ($promotion->discount_value ?? 0) / 100);
            return $promotion->max_discount_amount ? min($d, (float) $promotion->max_discount_amount) : $d;
        }
        if ($type === 'category_flat' || $type === 'category_percent') {
            $allowCats = collect($promotion->applicable_category_ids ?? [])->map(fn($v) => (int) $v)->all();
            $sub = $items->filter(function ($i) use ($productMap, $allowCats) {
                return in_array((int) ($productMap[(int) $i['product_id']] ?? 0), $allowCats, true);
            })->sum('line_total');
            if ($type === 'category_flat') return min($sub, (float) ($promotion->discount_value ?? 0));
            $d = $sub * ((float) ($promotion->discount_value ?? 0) / 100);
            return $promotion->max_discount_amount ? min($d, (float) $promotion->max_discount_amount) : $d;
        }
        return 0.0;
    }

    protected function buildStoreMenuData(int $storeId): array
    {
        $assigned = \App\Models\StoreMenuItem::where('store_id', $storeId)->where('is_active', 1)->get();
        $categoryIds = $assigned->pluck('category_id')->filter()->unique()->values();
        $explicitProductIds = $assigned->pluck('product_id')->filter()->unique()->values();
        $selectAllCats = $assigned->whereNull('product_id')->pluck('category_id')->filter()->unique()->values();

        $categories = \App\Models\MenuCategory::whereIn('id', $categoryIds)
            ->where('status', 1)
            ->orderBy('ordering')
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = collect();
        foreach ($categories as $cat) {
            $q = $cat->products()
                ->with(['productAttributes.attribute', 'productAttributes.attributeValue', 'productAddons.addon'])
                ->where('status', 1)
                ->orderBy('ordering')
                ->orderBy('name');
            if (! in_array($cat->id, $selectAllCats->all(), true)) {
                $q->whereIn('id', $explicitProductIds->all());
            }
            $products = $products->merge($q->get());
        }
        $products = $products->unique('id')->values();

        return [
            'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values(),
            'products' => $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category_id' => $p->category_id,
                    'base_price' => (float) $p->base_price,
                    'attributes' => $p->productAttributes
                        ->where('is_available', true)
                        ->groupBy('attribute_id')
                        ->map(function ($rows) {
                            $first = $rows->first();
                            return [
                                'attribute_name' => optional($first->attribute)->name,
                                'values' => $rows->map(function ($r) {
                                    return [
                                        'value' => optional($r->attributeValue)->value,
                                        'extra_price' => (float) ($r->price_override ?? optional($r->attributeValue)->extra_price ?? 0),
                                        'is_default' => (bool) $r->is_default,
                                    ];
                                })->values(),
                            ];
                        })->values(),
                    'addons' => $p->productAddons
                        ->where('is_available', true)
                        ->map(function ($r) {
                            return [
                                'name' => optional($r->addon)->name,
                                'extra_price' => (float) ($r->price_override ?? optional($r->addon)->price ?? 0),
                                'is_default' => (bool) $r->is_default,
                            ];
                        })->values(),
                ];
            })->values(),
        ];
    }
}
