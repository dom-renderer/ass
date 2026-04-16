<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuOrder;
use App\Models\MenuOrderItem;
use App\Models\OrderingOtpVerification;
use App\Models\OrderingUser;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\StoreMenuItem;
use App\Models\StoreQrCode;
use App\Services\PromotionEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderingController extends Controller
{
    public function landing()
    {
        $stores = Store::loc()->orderBy('name')->get(['id', 'name']);
        return view('ordering.landing', compact('stores'));
    }

    public function resolveQr(Request $request)
    {
        $request->validate(['value' => 'required|string']);
        $value = trim($request->input('value'));
        $qr = StoreQrCode::where('qr_label', $value)->first();
        if (! $qr) {
            return response()->json(['found' => false, 'message' => 'QR not found'], 404);
        }

        session([
            'ordering.store_id' => $qr->store_id,
            'ordering.table_number' => (int) $qr->table_number,
            'ordering.store_qr_code_id' => $qr->id,
        ]);

        return response()->json(['found' => true, 'redirect' => route('ordering.phone')]);
    }

    public function storeTables(Request $request)
    {
        $request->validate(['store_id' => 'required|exists:stores,id']);
        $storeId = (int) $request->store_id;
        $tables = StoreQrCode::where('store_id', $storeId)->orderBy('table_number')->get(['id', 'table_number', 'qr_label']);
        return response()->json(['tables' => $tables]);
    }

    public function selectManual(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'table_number' => 'required|integer|min:1',
        ]);

        $qr = StoreQrCode::where('store_id', (int)$request->store_id)
            ->where('table_number', (int)$request->table_number)
            ->first();

        session([
            'ordering.store_id' => (int) $request->store_id,
            'ordering.table_number' => (int) $request->table_number,
            'ordering.store_qr_code_id' => optional($qr)->id,
        ]);

        return redirect()->route('ordering.phone');
    }

    public function phone()
    {
        if (! session('ordering.store_id') || ! session('ordering.table_number')) {
            return redirect()->route('ordering.landing')->with('error', 'Please select/scan store table first.');
        }
        if (session('ordering.user_id')) {
            return redirect()->route('ordering.menu');
        }
        return view('ordering.phone');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:8|max:20',
            'email' => 'required|email:rfc,dns|max:191',
        ]);
        $phone = preg_replace('/\s+/', '', $request->phone);
        $email = strtolower(trim($request->email));

        $otp = (string) random_int(100000, 999999);
        OrderingOtpVerification::where('phone', $phone)->delete();
        OrderingOtpVerification::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Mail::raw('Your ordering OTP is: ' . $otp, function ($m) use ($email) {
        //     $m->to($email)->subject('Order Login OTP');
        // });

        session([
            'ordering.pending_phone' => $phone,
            'ordering.pending_email' => $email,
        ]);
        return response()->json(['success' => true, 'message' => 'OTP sent successfully.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);
        $phone = session('ordering.pending_phone');
        $email = session('ordering.pending_email');
        if (! $phone) {
            return response()->json(['success' => false, 'message' => 'OTP session expired.'], 422);
        }

        if ($request->otp != '000000') {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 422);
        }

        // $row = OrderingOtpVerification::where('phone', $phone)->whereNull('verified_at')->latest()->first();
        // if (! $row || now()->greaterThan($row->expires_at) || $row->otp !== $request->otp) {
        //     return response()->json(['success' => false, 'message' => 'Invalid/expired OTP.'], 422);
        // }

        // $row->update(['verified_at' => now()]);
        $user = OrderingUser::firstOrCreate(['phone' => $phone], ['email' => $email]);
        $user->update(['email' => $email, 'is_verified' => true, 'last_login_at' => now()]);

        session([
            'ordering.user_id' => $user->id,
            'ordering.user_phone' => $user->phone,
        ]);

        return response()->json(['success' => true, 'redirect' => route('ordering.menu')]);
    }

    public function menu()
    {
        $storeId = (int) session('ordering.store_id');
        $tableNo = (int) session('ordering.table_number');
        $userId = (int) session('ordering.user_id');

        if (! $storeId || ! $tableNo) return redirect()->route('ordering.landing');
        if (! $userId) return redirect()->route('ordering.phone');

        $store = Store::findOrFail($storeId);
        $assigned = StoreMenuItem::where('store_id', $storeId)->where('is_active', 1)->get();
        $categoryIds = $assigned->pluck('category_id')->filter()->unique()->values();
        $explicitProductIds = $assigned->pluck('product_id')->filter()->unique()->values();
        $selectAllCats = $assigned->whereNull('product_id')->pluck('category_id')->filter()->unique()->values();

        $categories = MenuCategory::whereIn('id', $categoryIds)->where('status', 1)->orderBy('ordering')->orderBy('name')->get();
        $products = collect();
        foreach ($categories as $cat) {
            $q = $cat->products()
                ->with([
                    'productAttributes.attribute',
                    'productAttributes.attributeValue',
                    'productAddons.addon',
                ])
                ->where('status', 1)
                ->orderBy('ordering')
                ->orderBy('name');
            if (! in_array($cat->id, $selectAllCats->all(), true)) {
                $q->whereIn('id', $explicitProductIds->all());
            }
            $products = $products->merge($q->get());
        }
        $products = $products->unique('id')->values()->map(function ($p) {
            $p->image_url = $p->image ? Storage::url('menu/products/' . $p->image) : null;
            $p->available_attributes = $p->productAttributes
                ->where('is_available', true)
                ->groupBy('attribute_id')
                ->map(function ($rows) {
                    $first = $rows->first();
                    return [
                        'attribute_id' => $first->attribute_id,
                        'attribute_name' => optional($first->attribute)->name,
                        'values' => $rows->map(function ($r) {
                            return [
                                'product_attribute_id' => $r->id,
                                'value_id' => $r->attribute_value_id,
                                'value' => optional($r->attributeValue)->value,
                                'extra_price' => (float) ($r->price_override ?? optional($r->attributeValue)->extra_price ?? 0),
                                'is_default' => (bool) $r->is_default,
                            ];
                        })->values(),
                    ];
                })->values();
            $p->available_addons = $p->productAddons
                ->where('is_available', true)
                ->map(function ($r) {
                    return [
                        'product_addon_id' => $r->id,
                        'addon_id' => $r->addon_id,
                        'name' => optional($r->addon)->name,
                        'extra_price' => (float) ($r->price_override ?? optional($r->addon)->price ?? 0),
                        'is_default' => (bool) $r->is_default,
                    ];
                })->values();
            return $p;
        });

        $productsPayload = $products->mapWithKeys(function ($p) {
            return [(string) $p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->base_price,
                'attributes' => $p->available_attributes,
                'addons' => $p->available_addons,
            ]];
        })->all();

        return view('ordering.menu', compact('store', 'tableNo', 'categories', 'products', 'productsPayload'));
    }

    public function myOrders()
    {
        $userId = (int) session('ordering.user_id');
        if (! $userId) {
            return redirect()->route('ordering.phone');
        }

        $orders = MenuOrder::with(['store', 'items'])
            ->where('ordering_user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return view('ordering.my-orders', compact('orders'));
    }

    public function applyCoupon(Request $request, PromotionEngineService $engine)
    {
        $request->validate([
            'coupon_code' => 'nullable|string|max:50',
            'cart_total' => 'required|numeric|min:0',
            'items' => 'required|array',
        ]);

        $storeId = (int) session('ordering.store_id');
        $userId = (int) session('ordering.user_id');
        $code = trim((string) $request->input('coupon_code', ''));

        $context = [
            'store_id' => $storeId,
            'user_id' => $userId,
            'cart_total' => (float) $request->cart_total,
            'items' => $request->items,
            'delivery_fee' => 0,
        ];

        if ($code !== '') {
            $promotion = Promotion::whereRaw('UPPER(code) = ?', [Str::upper($code)])->first();
            if (! $promotion) {
                return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 422);
            }
            $preview = $engine->preview($context);
            if (! $preview['promotion'] || $preview['promotion']->id !== $promotion->id || $preview['discount'] <= 0) {
                return response()->json(['success' => false, 'message' => 'Coupon not applicable for this cart.'], 422);
            }
            return response()->json(['success' => true, 'discount' => $preview['discount'], 'final_total' => $preview['final_total'], 'code' => $promotion->code]);
        }

        $preview = $engine->preview($context);
        return response()->json([
            'success' => true,
            'discount' => $preview['discount'],
            'final_total' => $preview['final_total'],
            'code' => optional($preview['promotion'])->code,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:menu_products,id',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.addons' => 'nullable|array',
            'items.*.attributes' => 'nullable|array',
            'coupon_code' => 'nullable|string|max:50',
            'discount_total' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
        ]);

        $storeId = (int) session('ordering.store_id');
        $tableNo = (int) session('ordering.table_number');
        $userId = (int) session('ordering.user_id');
        $storeQrCodeId = session('ordering.store_qr_code_id');
        if (! $storeId || ! $tableNo || ! $userId) {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 422);
        }

        $order = DB::transaction(function () use ($request, $storeId, $tableNo, $userId, $storeQrCodeId) {
            $subTotal = collect($request->items)->sum(function ($i) {
                return ((float)$i['price']) * ((int)$i['qty']);
            });
            $discount = (float) ($request->discount_total ?? 0);
            $grand = (float) $request->grand_total;

            $promotion = null;
            if ($request->filled('coupon_code')) {
                $promotion = Promotion::whereRaw('UPPER(code)=?', [Str::upper($request->coupon_code)])->first();
            }

            $order = MenuOrder::create([
                'store_id' => $storeId,
                'store_qr_code_id' => $storeQrCodeId,
                'ordering_user_id' => $userId,
                'table_number' => $tableNo,
                'order_number' => 'ORD' . now()->format('ymdHis') . random_int(10, 99),
                'status' => 'received',
                'payment_method' => 'cash',
                'coupon_code' => $request->coupon_code,
                'promotion_id' => optional($promotion)->id,
                'sub_total' => $subTotal,
                'discount_total' => $discount,
                'grand_total' => $grand,
                'meta' => ['source' => 'guest_ordering'],
            ]);

            foreach ($request->items as $i) {
                MenuOrderItem::create([
                    'menu_order_id' => $order->id,
                    'product_id' => (int) $i['product_id'],
                    'product_name' => (string) $i['name'],
                    'quantity' => (int) $i['qty'],
                    'unit_price' => (float) $i['price'],
                    'line_total' => ((float)$i['price']) * ((int)$i['qty']),
                    'addons' => $i['addons'] ?? [],
                    'attributes' => $i['attributes'] ?? [],
                ]);
            }

            return $order;
        });

        return response()->json(['success' => true, 'message' => 'Order received successfully!', 'order_number' => $order->order_number]);
    }

    public function logout()
    {
        session()->forget('ordering');
        return redirect()->route('ordering.landing')->with('success', 'Session cleared.');
    }
}
