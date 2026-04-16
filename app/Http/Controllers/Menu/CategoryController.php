<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreCategoryRequest;
use App\Http\Requests\Menu\UpdateCategoryRequest;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu_categories.view')->only(['index']);
        $this->middleware('permission:menu_categories.create')->only(['create', 'store']);
        $this->middleware('permission:menu_categories.edit')->only(['edit', 'update']);
        $this->middleware('permission:menu_categories.delete')->only(['destroy']);
        $this->middleware('permission:menu_categories.restore')->only(['restore']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MenuCategory::query()->select('menu_categories.*')->orderBy('ordering')->orderBy('name');

            if ($request->boolean('show_deleted')) {
                $query->onlyTrashed();
            }

            return datatables()
                ->eloquent($query)
                ->addColumn('image_thumb', function ($row) {
                    if (! empty($row->image)) {
                        return '<img src="' . e(Storage::url('menu/categories/' . $row->image)) . '" alt="" class="img-thumbnail" style="max-height:40px">';
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

                    if (auth()->user()->can('menu_categories.edit')) {
                        $html .= '<a href="' . route('menu.categories.edit', $row->id) . '" class="btn btn-sm btn-info me-1" title="Edit"><i class="bi bi-pencil"></i></a>';
                    }

                    if ($row->trashed()) {
                        if (auth()->user()->can('menu_categories.restore')) {
                            $html .= '<button type="button" class="btn btn-sm btn-secondary btn-restore-category me-1" data-id="' . $row->id . '" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>';
                        }
                    } else {
                        if (auth()->user()->can('menu_categories.delete')) {
                            $html .= '<button type="button" class="btn btn-sm btn-danger btn-delete-category" data-id="' . $row->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                        }
                    }

                    return $html;
                })
                ->rawColumns(['image_thumb', 'status_badge', 'action'])
                ->toJson();
        }

        $page_title = 'Menu Categories';

        return view('menu.categories.index', compact('page_title'));
    }

    public function create()
    {
        $page_title = 'Add Menu Category';

        return view('menu.categories.create', compact('page_title'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->hashName();
            $request->file('image')->storeAs('public/menu/categories', $data['image']);
        } else {
            unset($data['image']);
        }

        MenuCategory::create($data);

        return redirect()->route('menu.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(MenuCategory $category)
    {
        $page_title = 'Edit Menu Category';

        return view('menu.categories.edit', compact('page_title', 'category'));
    }

    public function update(UpdateCategoryRequest $request, MenuCategory $category)
    {
        $data = $request->validated();
        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete('menu/categories/' . $category->image);
            }
            $data['image'] = $request->file('image')->hashName();
            $request->file('image')->storeAs('public/menu/categories', $data['image']);
        } else {
            unset($data['image']);
        }

        $category->update($data);

        return redirect()->route('menu.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $category = MenuCategory::findOrFail($id);
        $category->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category deleted.']);
        }

        return redirect()->route('menu.categories.index')->with('success', 'Category deleted.');
    }

    public function restore(Request $request, $id)
    {
        $category = MenuCategory::onlyTrashed()->findOrFail($id);
        $category->restore();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category restored.']);
        }

        return redirect()->route('menu.categories.index')->with('success', 'Category restored.');
    }
}
