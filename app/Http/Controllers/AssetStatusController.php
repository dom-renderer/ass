<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetStatus;
use Illuminate\Validation\Rule;

class AssetStatusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
                ->eloquent(AssetStatus::query())
                ->addColumn('action', function ($row) {
                    $action = '';

                    if (auth()->user()->can('asset-statuses.edit')) {
                        $action .= '<a href="'.route('asset-statuses.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    if (auth()->user()->can('asset-statuses.destroy')) {
                        $action .= '<form method="POST" action="'.route("asset-statuses.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->editColumn('type', function($row) {
                    return $row->type == 1 ? 'Deployable' : 'Undeployable';
                })
                ->editColumn('color', function($row) {
                    return '<span class="badge" style="background-color: '.$row->color.'; color: #fff;">'.$row->color.'</span>';
                })
                ->rawColumns(['action', 'color'])
                ->toJson();
        }

        $page_title = 'Asset Status';
        $page_description = 'Manage Asset Statuses here';
        return view('asset-statuses.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Add Asset Status';
        return view('asset-statuses.create', compact('page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => [
                'required',
                Rule::unique('asset_statuses', 'title')->where(function ($query) use ($request) {
                    return $query->whereRaw('LOWER(title) = ?', [strtolower($request->title)]);
                })
            ],
            'color' => 'required',
            'type' => 'required|in:1,2'
        ]);

        AssetStatus::create([
            'title' => $request->title,
            'color' => $request->color,
            'type' => $request->type
        ]);

        return redirect()->route('asset-statuses.index')->with('success', 'Asset Status created successfully');
    }

    public function edit($id)
    {
        $page_title = 'Edit Asset Status';
        $assetStatus = AssetStatus::find(decrypt($id));

        return view('asset-statuses.edit', compact('assetStatus', 'page_title'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);

        $request->validate([
            'title' => [
                'required',
                Rule::unique('asset_statuses', 'title')->ignore($id)->where(function ($query) use ($request) {
                    return $query->whereRaw('LOWER(title) = ?', [strtolower($request->title)]);
                })
            ],
            'color' => 'required',
            'type' => 'required|in:1,2'
        ]);

        $assetStatus = AssetStatus::find($id);
        $assetStatus->update([
            'title' => $request->title,
            'color' => $request->color,
            'type' => $request->type
        ]);

        return redirect()->route('asset-statuses.index')->with('success', 'Asset Status updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $assetStatus = AssetStatus::find($id);
        $assetStatus->delete();

        return redirect()->route('asset-statuses.index')->with('success', 'Asset Status deleted successfully');
    }
}