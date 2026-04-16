<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuProduct;
use App\Models\MenuProductAddon;
use App\Models\MenuProductAttribute;
use App\Models\Store;
use App\Models\StoreMenuItem;
use App\Models\StoreMenuProductAddon;
use App\Models\StoreMenuProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:store_menu.view|store_menu.manage')->only(['index', 'getProducts', 'getProductConfig']);
        $this->middleware('permission:store_menu.manage')->only(['save']);
    }

    public function index(Store $location)
    {
        $page_title = 'Assign Menu — ' . $location->name;
        $categories = MenuCategory::where('status', 1)->orderBy('ordering')->orderBy('name')->get();

        $categoryIds = StoreMenuItem::where('store_id', $location->id)->distinct()->pluck('category_id')->filter()->values()->all();

        return view('locations.menu-assignment', compact('page_title', 'location', 'categories', 'categoryIds'));
    }

    public function getProducts(Request $request, Store $location)
    {
        $categoryIds = collect($request->input('category_ids', []))
            ->map(function ($id) { return (int) $id; })
            ->filter()
            ->unique()
            ->values();

        if ($categoryIds->isEmpty() && $request->filled('category_id')) {
            $categoryIds = collect([(int) $request->input('category_id')]);
        }

        if ($categoryIds->isEmpty()) {
            return response()->json(['categories' => []]);
        }

        $categories = MenuCategory::whereIn('id', $categoryIds->all())
            ->orderBy('ordering')
            ->orderBy('name')
            ->get(['id', 'name']);

        $savedItems = StoreMenuItem::where('store_id', $location->id)
            ->whereIn('category_id', $categoryIds->all())
            ->get(['category_id', 'product_id']);

        $wholeCategoryMap = $savedItems->whereNull('product_id')
            ->groupBy('category_id')
            ->map(function () { return true; });

        $selectedProductsMap = $savedItems->whereNotNull('product_id')
            ->groupBy('category_id')
            ->map(function ($rows) {
                return $rows->pluck('product_id')->map(function ($id) { return (int) $id; })->all();
            });

        $products = MenuProduct::whereIn('category_id', $categoryIds->all())
            ->where('status', 1)
            ->orderBy('ordering')
            ->orderBy('name')
            ->get(['id', 'name', 'category_id'])
            ->groupBy('category_id');

        return response()->json([
            'categories' => $categories->map(function ($cat) use ($products, $wholeCategoryMap, $selectedProductsMap) {
                $wholeCategory = (bool) $wholeCategoryMap->get($cat->id, false);
                $selectedProductIds = $selectedProductsMap->get($cat->id, []);
                $rows = ($products->get($cat->id) ?? collect())->map(function ($p) use ($wholeCategory, $selectedProductIds) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'selected' => $wholeCategory || in_array((int) $p->id, $selectedProductIds, true),
                    ];
                })->values();

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'select_all' => $wholeCategory,
                    'products' => $rows,
                ];
            })->values(),
        ]);
    }

    public function getProductConfig(Request $request, Store $location)
    {
        $request->validate(['product_id' => 'required|exists:menu_products,id']);
        $productId = (int) $request->product_id;

        $attrs = MenuProductAttribute::with(['attribute', 'attributeValue'])->where('product_id', $productId)->orderBy('attribute_id')->orderBy('id')->get();
        $addons = MenuProductAddon::with('addon')->where('product_id', $productId)->orderBy('id')->get();

        $savedAttrs = StoreMenuProductAttribute::where('store_id', $location->id)->where('product_id', $productId)->get()->keyBy('product_attribute_id');
        $savedAddons = StoreMenuProductAddon::where('store_id', $location->id)->where('product_id', $productId)->get()->keyBy('product_addon_id');

        return response()->json([
            'attributes' => $attrs->map(function ($r) use ($savedAttrs) {
                $saved = $savedAttrs->get($r->id);
                return [
                    'product_attribute_id' => $r->id,
                    'attribute_name' => optional($r->attribute)->name,
                    'value_name' => optional($r->attributeValue)->value,
                    'is_available' => $saved ? (bool) $saved->is_available : (bool) $r->is_available,
                    'is_default' => $saved ? (bool) $saved->is_default : (bool) $r->is_default,
                ];
            })->values(),
            'addons' => $addons->map(function ($r) use ($savedAddons) {
                $saved = $savedAddons->get($r->id);
                return [
                    'product_addon_id' => $r->id,
                    'addon_name' => optional($r->addon)->name,
                    'is_available' => $saved ? (bool) $saved->is_available : (bool) $r->is_available,
                    'is_default' => $saved ? (bool) $saved->is_default : (bool) $r->is_default,
                ];
            })->values(),
        ]);
    }

    public function save(Request $request, Store $location)
    {
        $request->validate([
            'categories' => 'nullable|array',
            'categories.*.category_id' => 'required|exists:menu_categories,id',
            'categories.*.select_all' => 'required|boolean',
            'categories.*.product_ids' => 'nullable|array',
            'categories.*.product_ids.*' => 'exists:menu_products,id',
            'product_configs' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $location) {
            StoreMenuItem::where('store_id', $location->id)->forceDelete();
            StoreMenuProductAttribute::where('store_id', $location->id)->forceDelete();
            StoreMenuProductAddon::where('store_id', $location->id)->forceDelete();

            foreach (($request->input('categories') ?? []) as $block) {
                $cid = (int) $block['category_id'];
                $selectAll = filter_var($block['select_all'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($selectAll) {
                    StoreMenuItem::create([
                        'store_id' => $location->id,
                        'category_id' => $cid,
                        'product_id' => null,
                        'is_active' => 1,
                    ]);
                    continue;
                }

                foreach (($block['product_ids'] ?? []) as $pid) {
                    StoreMenuItem::create([
                        'store_id' => $location->id,
                        'category_id' => $cid,
                        'product_id' => (int) $pid,
                        'is_active' => 1,
                    ]);
                }
            }

            foreach (($request->input('product_configs') ?? []) as $productId => $cfg) {
                $productId = (int) $productId;

                foreach (($cfg['attributes'] ?? []) as $row) {
                    if (empty($row['product_attribute_id'])) {
                        continue;
                    }
                    StoreMenuProductAttribute::create([
                        'store_id' => $location->id,
                        'product_id' => $productId,
                        'product_attribute_id' => (int) $row['product_attribute_id'],
                        'is_available' => !empty($row['is_available']) ? 1 : 0,
                        'is_default' => !empty($row['is_default']) ? 1 : 0,
                    ]);
                }

                foreach (($cfg['addons'] ?? []) as $row) {
                    if (empty($row['product_addon_id'])) {
                        continue;
                    }
                    StoreMenuProductAddon::create([
                        'store_id' => $location->id,
                        'product_id' => $productId,
                        'product_addon_id' => (int) $row['product_addon_id'],
                        'is_available' => !empty($row['is_available']) ? 1 : 0,
                        'is_default' => !empty($row['is_default']) ? 1 : 0,
                    ]);
                }
            }
        });

        return redirect()->route('locations.menu-assignment.index', $location->id)->with('success', 'Menu assignment saved.');
    }
}
