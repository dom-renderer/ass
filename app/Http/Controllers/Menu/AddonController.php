<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreAddonRequest;
use App\Http\Requests\Menu\UpdateAddonRequest;
use App\Models\MenuAddon;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu_addons.view')->only(['index']);
        $this->middleware('permission:menu_addons.create')->only(['create', 'store']);
        $this->middleware('permission:menu_addons.edit')->only(['edit', 'update']);
        $this->middleware('permission:menu_addons.delete')->only(['destroy']);
        $this->middleware('permission:menu_addons.restore')->only(['restore']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MenuAddon::query()->select('menu_addons.*')->orderBy('name');

            if ($request->boolean('show_deleted')) {
                $query->onlyTrashed();
            }

            return datatables()
                ->eloquent($query)
                ->addColumn('status_badge', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    if (auth()->user()->can('menu_addons.edit')) {
                        $html .= '<a href="' . route('menu.addons.edit', $row->id) . '" class="btn btn-sm btn-info me-1"><i class="bi bi-pencil"></i></a>';
                    }

                    if ($row->trashed()) {
                        if (auth()->user()->can('menu_addons.restore')) {
                            $html .= '<button type="button" class="btn btn-sm btn-secondary btn-restore-addon me-1" data-id="' . $row->id . '"><i class="bi bi-arrow-counterclockwise"></i></button>';
                        }
                    } else {
                        if (auth()->user()->can('menu_addons.delete')) {
                            $html .= '<button type="button" class="btn btn-sm btn-danger btn-delete-addon" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                        }
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->toJson();
        }

        $page_title = 'Menu Addons';

        return view('menu.addons.index', compact('page_title'));
    }

    public function create()
    {
        $page_title = 'Add Menu Addon';

        return view('menu.addons.create', compact('page_title'));
    }

    public function store(StoreAddonRequest $request)
    {
        $validated = $request->validated();

        if (! empty($validated['addons']) && is_array($validated['addons'])) {
            foreach ($validated['addons'] as $row) {
                MenuAddon::create([
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'description' => $row['description'] ?? null,
                    'status' => $row['status'],
                ]);
            }
        } else {
            MenuAddon::create([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 1,
            ]);
        }

        return redirect()->route('menu.addons.index')->with('success', 'Addon created successfully.');
    }

    public function edit(MenuAddon $addon)
    {
        $page_title = 'Edit Menu Addon';

        return view('menu.addons.edit', compact('page_title', 'addon'));
    }

    public function update(UpdateAddonRequest $request, MenuAddon $addon)
    {
        $addon->update($request->validated());

        return redirect()->route('menu.addons.index')->with('success', 'Addon updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $addon = MenuAddon::findOrFail($id);
        $addon->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('menu.addons.index')->with('success', 'Addon deleted.');
    }

    public function restore(Request $request, $id)
    {
        $addon = MenuAddon::onlyTrashed()->findOrFail($id);
        $addon->restore();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('menu.addons.index')->with('success', 'Addon restored.');
    }
}
