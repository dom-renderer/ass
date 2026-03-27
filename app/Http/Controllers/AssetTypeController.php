<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\StoreType;
use App\Models\Store;

class AssetTypeController extends Controller
{
    public function index(Request $request)
    {   
        if ($request->ajax()) {

            return datatables()
            ->eloquent(StoreType::withoutGlobalScope('os')->ass())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('assets-types.show')) {
                    $action .= '<a href="'.route("assets-types.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('assets-types.edit')) {
                    $action .= '<a href="'.route('assets-types.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('assets-types.destroy')) {
                    $action .= '<form method="POST" action="'.route("assets-types.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Asset Model';
        $page_description = 'Manage Asset model here';
        return view('asset-types.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Asset Model Add';

        return view('asset-types.create', compact( 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', Rule::unique('store_types', 'name')->where(function ($query) {
                return $query->where('type', 1)->whereNull( 'deleted_at' );
            })
            ]
        ]);
    
        StoreType::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => 1
        ]);
    
        return redirect()->route('assets-types.index')->with('success','Store Type created successfully');
    }

    public function show($id)
    {
        $page_title = 'Asset Model Show';
        $storetype = StoreType::withoutGlobalScope('os')->ass()->find(decrypt($id));
    
        return view('asset-types.show', compact('storetype', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Asset Model Edit';
        $storetype = StoreType::withoutGlobalScope('os')->ass()->find(decrypt($id));
    
        return view('asset-types.edit', compact('storetype', 'page_title', 'id'));
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => ['required', Rule::unique('store_types', 'name')
            ->ignore($cId)
            ->where(function ($query) {
                return $query->where('type', 1)->whereNull( 'deleted_at' );
            })
            ]
        ]);

        $storetype = StoreType::withoutGlobalScope('os')->ass()->find($cId);
        $storetype->update($request->only(['name', 'description']));
    
        return redirect()->route('assets-types.index')->with('success','Store Model updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);

        if (Store::where('store_type', $id)->exists()) {
            return redirect()->route('assets-types.index')->with('success','There are some stores exists with this store model.');
        }

        $storetype = StoreType::withoutGlobalScope('os')->ass()->find($id);
        $storetype->delete();
        
        return redirect()->route('assets-types.index')->with('success','Store Model deleted successfully');
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
    
        $query = StoreType::withoutGlobalScope('os')->ass();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}