<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use App\Models\Store;

class AssetCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
            ->eloquent(StoreCategory::withoutGlobalScope('os')->ass())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('assets-categories.show')) {
                    $action .= '<a href="'.route("assets-categories.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('assets-categories.edit')) {
                    $action .= '<a href="'.route('assets-categories.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('assets-categories.destroy')) {
                    $action .= '<form method="POST" action="'.route("assets-categories.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Assets Categories';
        $page_description = 'Manage assets categories here';
        return view('asset-categories.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Asset Category Add';

        return view('asset-categories.create', compact( 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique( 'store_categories', 'name' )->where('type', 1)->whereNull( 'deleted_at' ),
            ],
        ]);
    
        StoreCategory::create([
            'name' => $request->name,
            'type' => 1
        ]);
    
        return redirect()->route('assets-categories.index')->with('success','Asset Category created successfully');
    }

    public function show($id)
    {
        $page_title = 'Asset Category Show';
        $storecategory = StoreCategory::withoutGlobalScope('os')->ass()->find(decrypt($id));
    
        return view('asset-categories.show', compact('storecategory', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Asset Category Edit';
        $storecategory = StoreCategory::withoutGlobalScope('os')->ass()->find(decrypt($id));
    
        return view('asset-categories.edit', compact('storecategory', 'page_title', 'id'));
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('store_categories', 'name')->where('type', 1)->whereNull('deleted_at')->ignore($cId),
            ],
        ]);

        $storecategory = StoreCategory::withoutGlobalScope('os')->ass()->find($cId);
        $storecategory->update( $request->only( [ 'name' ] ) );
    
        return redirect()->route('assets-categories.index')->with('success','Asset Category updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);

        if ( Store::withoutGlobalScope('os')->ass()->where( 'store_category', $id )->exists() ) {
            return redirect()->route( 'store-categories.index' )->with( 'error', 'There are some assets exists with this asset category.' );
        }

        $storecategory = StoreCategory::withoutGlobalScope('os')->ass()->find($id);
        $storecategory->delete();
        
        return redirect()->route('assets-categories.index')->with('success','Asset Category deleted successfully');
    }

}