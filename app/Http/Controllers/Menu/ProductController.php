<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreProductRequest;
use App\Http\Requests\Menu\UpdateProductRequest;
use App\Models\MenuAddon;
use App\Models\MenuAttribute;
use App\Models\MenuCategory;
use App\Models\MenuProduct;
use App\Models\MenuProductAddon;
use App\Models\MenuProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu_products.view')->only(['index']);
        $this->middleware('permission:menu_products.create')->only(['create', 'store']);
        $this->middleware('permission:menu_products.edit')->only(['edit', 'update']);
        $this->middleware('permission:menu_products.delete')->only(['destroy']);
        $this->middleware('permission:menu_products.restore')->only(['restore']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MenuProduct::query()->with('category')->orderBy('menu_products.ordering')->orderBy('menu_products.name');

            if ($request->boolean('show_deleted')) {
                $query->onlyTrashed();
            }

            return datatables()
                ->eloquent($query)
                ->addColumn('category_name', function ($row) {
                    return $row->category ? e($row->category->name) : '';
                })
                ->addColumn('image_thumb', function ($row) {
                    if (! empty($row->image)) {
                        return '<img src="' . e(Storage::url('menu/products/' . $row->image)) . '" alt="" class="img-thumbnail" style="max-height:40px">';
                    }

                    return '<span class="text-muted">—</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    if (auth()->user()->can('menu_products.edit')) {
                        $html .= '<a href="' . route('menu.products.edit', $row->id) . '" class="btn btn-sm btn-info me-1"><i class="bi bi-pencil"></i></a>';
                    }

                    if ($row->trashed()) {
                        if (auth()->user()->can('menu_products.restore')) {
                            $html .= '<button type="button" class="btn btn-sm btn-secondary btn-restore-product me-1" data-id="' . $row->id . '"><i class="bi bi-arrow-counterclockwise"></i></button>';
                        }
                    } else {
                        if (auth()->user()->can('menu_products.delete')) {
                            $html .= '<button type="button" class="btn btn-sm btn-danger btn-delete-product" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                        }
                    }

                    return $html;
                })
                ->rawColumns(['image_thumb', 'status_badge', 'action'])
                ->toJson();
        }

        $page_title = 'Menu Products';

        return view('menu.products.index', compact('page_title'));
    }

    public function create()
    {
        $page_title = 'Add Menu Product';
        $categories = MenuCategory::orderBy('ordering')->orderBy('name')->get();
        $attributes = MenuAttribute::with(['values' => function ($q) {
            $q->orderBy('ordering');
        }])->where('status', 1)->orderBy('name')->get();
        $addons = MenuAddon::where('status', 1)->orderBy('name')->get();

        return view('menu.products.create', compact('page_title', 'categories', 'attributes', 'addons'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $this->buildProductPayload($request, null);

        DB::transaction(function () use ($data, $request) {
            $product = MenuProduct::create($data);
            $this->syncProductRelations($product, $request);
        });

        return redirect()->route('menu.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(MenuProduct $product)
    {
        $page_title = 'Edit Menu Product';
        $categories = MenuCategory::orderBy('ordering')->orderBy('name')->get();
        $attributes = MenuAttribute::with(['values' => function ($q) {
            $q->orderBy('ordering');
        }])->where('status', 1)->orderBy('name')->get();
        $addons = MenuAddon::where('status', 1)->orderBy('name')->get();

        $product->load(['productAttributes', 'productAddons']);

        $existingProductAttributes = $product->productAttributes->map(function ($r) {
            return [
                'attribute_id' => $r->attribute_id,
                'attribute_value_id' => $r->attribute_value_id,
                'price_override' => $r->price_override,
                'is_available' => (bool) $r->is_available,
                'is_default' => (bool) $r->is_default,
            ];
        })->values()->all();

        $existingProductAddons = $product->productAddons->keyBy('addon_id');
        $existingAddonPrices = $existingProductAddons->mapWithKeys(function ($row, $id) {
            return [(string) $id => [
                'price_override' => $row->price_override,
                'is_available' => (bool) $row->is_available,
                'is_default' => (bool) $row->is_default,
            ]];
        })->all();

        return view('menu.products.edit', compact(
            'page_title',
            'categories',
            'attributes',
            'addons',
            'product',
            'existingProductAttributes',
            'existingProductAddons',
            'existingAddonPrices'
        ));
    }

    public function update(UpdateProductRequest $request, MenuProduct $product)
    {
        $data = $this->buildProductPayload($request, $product);

        DB::transaction(function () use ($data, $request, $product) {
            $product->update($data);
            $this->syncProductRelations($product, $request);
        });

        return redirect()->route('menu.products.index')->with('success', 'Product updated successfully.');
    }

    protected function buildProductPayload(Request $request, ?MenuProduct $existing = null): array
    {
        $data = $request->validated();
        unset($data['image'], $data['product_attributes'], $data['product_addons']);

        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        if ($request->hasFile('image')) {
            if ($existing && $existing->image) {
                Storage::disk('public')->delete('menu/products/' . $existing->image);
            }
            $data['image'] = $request->file('image')->hashName();
            $request->file('image')->storeAs('public/menu/products', $data['image']);
        }

        return $data;
    }

    protected function syncProductRelations(MenuProduct $product, Request $request): void
    {
        MenuProductAttribute::where('product_id', $product->id)->forceDelete();
        MenuProductAddon::where('product_id', $product->id)->forceDelete();

        foreach ($request->input('product_attributes', []) as $row) {
            if (empty($row['attribute_id']) || empty($row['attribute_value_id'])) {
                continue;
            }

            MenuProductAttribute::create([
                'product_id' => $product->id,
                'attribute_id' => $row['attribute_id'],
                'attribute_value_id' => $row['attribute_value_id'],
                'price_override' => isset($row['price_override']) && $row['price_override'] !== '' ? $row['price_override'] : null,
                'is_available' => array_key_exists('is_available', $row) ? (int) (bool) $row['is_available'] : 1,
                'is_default' => !empty($row['is_default']) ? 1 : 0,
            ]);
        }

        foreach ($request->input('product_addons', []) as $row) {
            if (empty($row['addon_id'])) {
                continue;
            }

            MenuProductAddon::create([
                'product_id' => $product->id,
                'addon_id' => $row['addon_id'],
                'price_override' => isset($row['price_override']) && $row['price_override'] !== '' ? $row['price_override'] : null,
                'is_available' => array_key_exists('is_available', $row) ? (int) (bool) $row['is_available'] : 1,
                'is_default' => !empty($row['is_default']) ? 1 : 0,
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $product = MenuProduct::findOrFail($id);
        $product->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('menu.products.index')->with('success', 'Product deleted.');
    }

    public function restore(Request $request, $id)
    {
        $product = MenuProduct::onlyTrashed()->findOrFail($id);
        $product->restore();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('menu.products.index')->with('success', 'Product restored.');
    }
}
