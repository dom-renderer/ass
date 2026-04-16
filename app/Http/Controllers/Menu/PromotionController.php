<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StorePromotionRequest;
use App\Http\Requests\Menu\UpdatePromotionRequest;
use App\Models\MenuCategory;
use App\Models\MenuProduct;
use App\Models\Promotion;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu_promotions.view')->only(['index']);
        $this->middleware('permission:menu_promotions.create')->only(['create', 'store']);
        $this->middleware('permission:menu_promotions.edit')->only(['edit', 'update']);
        $this->middleware('permission:menu_promotions.delete')->only(['destroy']);
        $this->middleware('permission:menu_promotions.restore')->only(['restore']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Promotion::withTrashed()->orderByDesc('priority')->orderByDesc('id');
            return datatables()->eloquent($query)
                ->addColumn('scope', fn($r) => $r->is_global ? '<span class="badge bg-primary">All Stores</span>' : '<span class="badge bg-info">Store-wise</span>')
                ->addColumn('status_badge', fn($r) => $r->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($r) {
                    $h = '';
                    if (auth()->user()->can('menu_promotions.edit')) $h .= '<a href="'.route('menu.promotions.edit',$r->id).'" class="btn btn-sm btn-info me-1"><i class="bi bi-pencil"></i></a>';
                    if ($r->trashed()) {
                        if (auth()->user()->can('menu_promotions.restore')) $h .= '<button type="button" class="btn btn-sm btn-secondary btn-restore-promotion" data-id="'.$r->id.'"><i class="bi bi-arrow-counterclockwise"></i></button>';
                    } else {
                        if (auth()->user()->can('menu_promotions.delete')) $h .= '<button type="button" class="btn btn-sm btn-danger btn-delete-promotion" data-id="'.$r->id.'"><i class="bi bi-trash"></i></button>';
                    }
                    return $h;
                })->rawColumns(['scope','status_badge','action'])->toJson();
        }

        $page_title = 'Promotions & Coupons';
        return view('menu.promotions.index', compact('page_title'));
    }

    public function create()
    {
        $page_title = 'Create Promotion';
        $promotion = new Promotion();
        $categories = MenuCategory::where('status',1)->orderBy('name')->get(['id','name']);
        $products = MenuProduct::where('status',1)->orderBy('name')->get(['id','name']);
        $stores = Store::loc()->orderBy('name')->get(['id','name']);
        $selectedStores = [];
        return view('menu.promotions.create', compact('page_title','promotion','categories','products','stores','selectedStores'));
    }

    public function store(StorePromotionRequest $request)
    {
        $promotion = Promotion::create($this->payload($request));
        $promotion->stores()->sync($request->boolean('is_global') ? [] : ($request->input('store_ids', [])));
        return redirect()->route('menu.promotions.index')->with('success', 'Promotion created successfully.');
    }

    public function edit(Promotion $promotion)
    {
        $page_title = 'Edit Promotion';
        $categories = MenuCategory::where('status',1)->orderBy('name')->get(['id','name']);
        $products = MenuProduct::where('status',1)->orderBy('name')->get(['id','name']);
        $stores = Store::loc()->orderBy('name')->get(['id','name']);
        $selectedStores = $promotion->stores()->pluck('stores.id')->map(fn($v)=>(string)$v)->all();
        return view('menu.promotions.edit', compact('page_title','promotion','categories','products','stores','selectedStores'));
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        $promotion->update($this->payload($request));
        $promotion->stores()->sync($request->boolean('is_global') ? [] : ($request->input('store_ids', [])));
        return redirect()->route('menu.promotions.index')->with('success', 'Promotion updated successfully.');
    }

    protected function payload(Request $request): array
    {
        $data = $request->validated();
        $data['code'] = Str::upper(trim($data['code']));
        $data['is_auto_apply'] = $request->boolean('is_auto_apply');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_stackable'] = $request->boolean('is_stackable');
        $data['is_global'] = $request->boolean('is_global', true);
        $data['priority'] = (int)($data['priority'] ?? 0);
        $ruleBuilder = [];
        if ($request->filled('rule_builder')) {
            $decoded = json_decode((string) $request->input('rule_builder'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $ruleBuilder = $decoded;
            }
        }
        $data['meta'] = ['ui_version' => 2, 'rule_builder' => $ruleBuilder];
        unset($data['rule_builder']);
        return $data;
    }

    public function destroy(Request $request, $id)
    {
        Promotion::findOrFail($id)->delete();
        return $request->ajax() ? response()->json(['success'=>true]) : back();
    }

    public function restore(Request $request, $id)
    {
        Promotion::onlyTrashed()->findOrFail($id)->restore();
        return $request->ajax() ? response()->json(['success'=>true]) : back();
    }
}
