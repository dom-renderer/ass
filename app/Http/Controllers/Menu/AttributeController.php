<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreAttributeRequest;
use App\Http\Requests\Menu\UpdateAttributeRequest;
use App\Models\MenuAttribute;
use App\Models\MenuAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu_attributes.view')->only(['index']);
        $this->middleware('permission:menu_attributes.create')->only(['create', 'store']);
        $this->middleware('permission:menu_attributes.edit')->only(['edit', 'update']);
        $this->middleware('permission:menu_attributes.delete')->only(['destroy']);
        $this->middleware('permission:menu_attributes.restore')->only(['restore']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MenuAttribute::query()->select('menu_attributes.*')->orderBy('name');

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

                    if (auth()->user()->can('menu_attributes.edit')) {
                        $html .= '<a href="' . route('menu.attributes.edit', $row->id) . '" class="btn btn-sm btn-info me-1"><i class="bi bi-pencil"></i></a>';
                    }

                    if ($row->trashed()) {
                        if (auth()->user()->can('menu_attributes.restore')) {
                            $html .= '<button type="button" class="btn btn-sm btn-secondary btn-restore-attr me-1" data-id="' . $row->id . '"><i class="bi bi-arrow-counterclockwise"></i></button>';
                        }
                    } else {
                        if (auth()->user()->can('menu_attributes.delete')) {
                            $html .= '<button type="button" class="btn btn-sm btn-danger btn-delete-attr" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                        }
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->toJson();
        }

        $page_title = 'Menu Attributes';

        return view('menu.attributes.index', compact('page_title'));
    }

    public function create()
    {
        $page_title = 'Add Menu Attribute';

        return view('menu.attributes.create', compact('page_title'));
    }

    public function store(StoreAttributeRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $attr = MenuAttribute::create([
                'name' => $data['name'],
                'status' => $data['status'],
            ]);

            $this->syncValues($attr, $request->input('values', []));
        });

        return redirect()->route('menu.attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function edit(MenuAttribute $attribute)
    {
        $page_title = 'Edit Menu Attribute';
        $attribute->load('values');

        $valueRows = $attribute->values->map(function ($v) {
            return [
                'id' => $v->id,
                'value' => $v->value,
                'extra_price' => (string) $v->extra_price,
                'ordering' => $v->ordering,
            ];
        })->values()->all();

        return view('menu.attributes.edit', compact('page_title', 'attribute', 'valueRows'));
    }

    public function update(UpdateAttributeRequest $request, MenuAttribute $attribute)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $attribute) {
            $attribute->update([
                'name' => $data['name'],
                'status' => $data['status'],
            ]);

            $this->syncValues($attribute, $request->input('values', []));
        });

        return redirect()->route('menu.attributes.index')->with('success', 'Attribute updated successfully.');
    }

    protected function syncValues(MenuAttribute $attribute, array $values): void
    {
        $keptIds = [];

        foreach ($values as $row) {
            if (empty($row['value'])) {
                continue;
            }

            $payload = [
                'value' => $row['value'],
                'extra_price' => isset($row['extra_price']) ? $row['extra_price'] : 0,
                'ordering' => isset($row['ordering']) ? (int) $row['ordering'] : 0,
            ];

            if (! empty($row['id'])) {
                $val = MenuAttributeValue::where('attribute_id', $attribute->id)->whereKey($row['id'])->first();
                if ($val) {
                    $val->update($payload);
                    $keptIds[] = $val->id;
                }
            } else {
                $created = $attribute->values()->create($payload);
                $keptIds[] = $created->id;
            }
        }

        if (empty($keptIds)) {
            MenuAttributeValue::where('attribute_id', $attribute->id)->delete();
        } else {
            MenuAttributeValue::where('attribute_id', $attribute->id)->whereNotIn('id', $keptIds)->delete();
        }
    }

    public function destroy(Request $request, $id)
    {
        $attribute = MenuAttribute::findOrFail($id);
        $attribute->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('menu.attributes.index')->with('success', 'Attribute deleted.');
    }

    public function restore(Request $request, $id)
    {
        $attribute = MenuAttribute::onlyTrashed()->findOrFail($id);
        $attribute->restore();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('menu.attributes.index')->with('success', 'Attribute restored.');
    }
}
