<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use App\Imports\LocationImport;
use App\Models\LocationAsset;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Department;
use App\Models\UserStore;
use App\Models\ModelType;
use App\Models\StoreType;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\City;
use App\Models\User;
use App\Models\AssetStatus;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{
    public function index() {
        $page_title = "Assets";
        $stores = Store::with(['dom', 'assetStatus', 'store'])
        ->withoutGlobalScope('os')->ass()
        ->when(!empty(request('filter_code')), function ($builder) {
            $builder->where('code', request('filter_code'));
        })
        ->when(!empty(request('filter_ucode')), function ($builder) {
            $builder->where('ucode', request('filter_ucode'));
        })
        ->when(!empty(request('filter_name')) && request('filter_name') != 'all', function ($builder) {
            $builder->where('name', 'LIKE', '%' . request('filter_name') . '%');
        })
        ->when(!empty(request('filter_location')) && request('filter_location') != 'all', function ($builder) {
            $builder->where('location', request('filter_location'));
        })
        ->when(!empty(request('filter_dom')) && request('filter_dom') != 'all', function ($builder) {
            $builder->where('dom_id', request('filter_dom'));
        })
        ->when(!empty(request('filter_status')) && request('filter_status') != 'all', function ($builder) {
            $builder->where('asset_status_id', request('filter_status'));
        })
        ->paginate(12)
        ->withQueryString();

        $storeTypes = StoreType::withoutGlobalScope('os')->ass()->get();
        $modelTypes = ModelType::withoutGlobalScope('os')->ass()->get();
        $storeCategories = StoreCategory::withoutGlobalScope('os')->ass()->get();
        $allStores = Store::get();

        $locationFilter = Store::select('name')->where('id', request('filter_location'))->first();
        $domFilter = User::whereHas('roles', function ($builder) {
            $builder->where('id', Helper::$roles['divisional-operations-manager']);
        })->select('employee_id', 'name', 'middle_name', 'last_name')->where('id', request('filter_dom'))->first();

        $assetStatuses = AssetStatus::all();

        return view( 'assets.index', compact( 'page_title', 'stores', 'storeTypes', 'modelTypes', 'locationFilter', 'domFilter', 'storeCategories', 'allStores', 'assetStatuses' ) );
    }

    public function store(Request $request) {

        $request->validate([
            'store_type' => 'required',
            'model_type' => 'required',
            'store_category' => 'required',
            'name' => [
                'required',
                Rule::unique('stores', 'name')->where(function ($query) {
                    $query->where('type', 1)->whereNull( 'deleted_at' );
                }),
            ],
            'ucode' => [
                'required',
                Rule::unique('stores', 'ucode')->where(function ($query) {
                    $query->where('type', 1)->whereNull( 'deleted_at' );
                }),
            ],
            'po_date' => 'required',
            'warranty' => 'required|min:1',
            'lifespan' => 'required|min:1',
            'asset_status_id' => 'nullable|exists:asset_statuses,id',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'document_titles' => 'nullable|array',
            'document_titles.*' => 'nullable|string|max:255',
        ]);

        $this->validateDocumentTitlesForCreate($request);

        $uploadedDocuments = $this->uploadDocuments($request, 'documents', 'document_titles');

        $store = Store::create([
            'type' => 1,
            'store_type' => $request->store_type,
            'model_type' => $request->model_type,
            'store_category' => $request->store_category,
            'name' => $request->name,
            'code' => $request->code,
            'ucode' => $request->ucode,
            'dom_id' => $request->dom_id,
            'location' => $request->location_item,
            'po_date' => date("Y-m-d H:i:s", strtotime($request->po_date)),
            'warranty' => $request->warranty,
            'lifespan' => $request->lifespan,
            'primary_image' => $this->uploadFile($request, 'primary_image'),
            'secondary_images' => $this->uploadFiles($request, 'secondary_images'),
            'documents' => $uploadedDocuments['documents'],
            'document_titles' => $uploadedDocuments['document_titles'],
            'asset_status_id' => $request->asset_status_id
        ]);

        if ($request->has('assets') && is_array($request->assets)) {
            foreach ($request->assets as $assetData) {
                if (!empty($assetData['asset_id'])) {
                    LocationAsset::create([
                        'location_id' => $store->id,
                        'asset_id' => $assetData['asset_id'],
                        'description' => $assetData['description'] ?? null
                    ]);
                }
            }
        }

        return redirect()->route('assets.index')->with('success', 'Location created successfully');
    }

    public function create(Request $request) {
        $page_title = "Add Asset";
        $storeTypes = StoreType::withoutGlobalScope('os')->ass()->get();
        $modelTypes = ModelType::withoutGlobalScope('os')->ass()->get();
        $storeCategories = StoreCategory::withoutGlobalScope('os')->ass()->get();

        $storeCategories = StoreCategory::withoutGlobalScope('os')->ass()->get();
        $assetStatuses = AssetStatus::all();

        return view( 'assets.create', compact( 'page_title', 'storeTypes', 'modelTypes', 'storeCategories', 'assetStatuses' ) );
    }

    public function edit(Request $request, $id) {
        $page_title = "Edit Asset";
        $store = Store::withoutGlobalScope('os')->ass()->find($id);
        $storeTypes = StoreType::withoutGlobalScope('os')->ass()->get();
        $modelTypes = ModelType::withoutGlobalScope('os')->ass()->get();
        $storeCategories = StoreCategory::withoutGlobalScope('os')->ass()->get();
        $locationItems = Store::where('id', $store->location)->get();
        $assignTo = User::where('id', $store->dom_id)->get();
        $locationAssets = LocationAsset::where('location_id', $id)->with('asset')->get();
        $assetStatuses = AssetStatus::all();

        return view( 'assets.edit', compact( 'page_title', 'store', 'storeTypes', 'modelTypes', 'storeCategories', 'locationItems', 'assignTo', 'locationAssets', 'assetStatuses' ) );
    }

    public function update(Request $request, $stores) {
        $thisStore = Store::withoutGlobalScope('os')->find($stores);

        $request->validate([
            'store_type' => 'required',
            'model_type' => 'required',
            'store_category' => 'required',
            'name' => [
                'required',
                Rule::unique('stores', 'name')->where(function ($query) {
                    $query->where('type', 1)->whereNull( 'deleted_at' );
                })->ignore($stores),
            ],
            'ucode' => [
                'required',
                Rule::unique('stores', 'ucode')->where(function ($query) {
                    $query->where('type', 1)->whereNull( 'deleted_at' );
                })->ignore($stores),
            ],
            'po_date' => 'required',
            'warranty' => 'required|min:1',
            'lifespan' => 'required|min:1',
            'asset_status_id' => 'nullable|exists:asset_statuses,id',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'document_titles' => 'nullable|array',
            'document_titles.*' => 'nullable|string|max:255',
            'existing_document_titles' => 'nullable|array',
        ]);

        $this->validateDocumentTitlesForUpdate($request, $thisStore);

        $documentsPayload = $this->handleDocumentsUpdate($request, $stores);
        $thisStore->update([
            'store_type' => $request->store_type,
            'model_type' => $request->model_type,
            'store_category' => !empty($request->store_category) ? $request->store_category : null,
            'name' => $request->name,
            'code' => $request->code,
            'ucode' => $request->ucode,
            'dom_id' => $request->dom_id,
            'location' => $request->location_item,
            'po_date' => date("Y-m-d H:i:s", strtotime($request->po_date)),
            'warranty' => $request->warranty,
            'lifespan' => $request->lifespan,
            'primary_image' => $this->handlePrimaryImageUpdate($request, $stores),
            'secondary_images' => $this->handleSecondaryImagesUpdate($request, $stores),
            'documents' => $documentsPayload['documents'],
            'document_titles' => $documentsPayload['document_titles'],
            'asset_status_id' => $request->asset_status_id
        ]);

        $existingAssetIds = [];
        if ($request->has('assets') && is_array($request->assets)) {
            foreach ($request->assets as $assetData) {
                if (!empty($assetData['asset_id'])) {
                    $existingAssetIds[] = $assetData['asset_id'];
                    LocationAsset::updateOrCreate(
                        [
                            'location_id' => $stores,
                            'asset_id' => $assetData['asset_id']
                        ],
                        [
                            'description' => $assetData['description'] ?? null
                        ]
                    );
                }
            }
        }

        LocationAsset::where('location_id', $stores)
            ->whereNotIn('asset_id', $existingAssetIds)
            ->delete();

        return redirect()->route('assets.index')->with('success', 'Location updated successfully');
    }

    private function uploadDocuments($request, $key, $titleKey = 'document_titles') {
        $files = [];
        $titlesMap = [];
        $titles = $request->input($titleKey, []);
        $titleIndex = 0;
        if ($request->hasFile($key)) {
            foreach ($request->file($key) as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/asset-documents', $filename);
                $files[] = $filename;
                $titlesMap[$filename] = trim((string) ($titles[$titleIndex] ?? ''));
                $titleIndex++;
            }
        }
        return [
            'documents' => !empty($files) ? $files : null,
            'document_titles' => !empty($titlesMap) ? $titlesMap : null,
        ];
    }

    private function handleDocumentsUpdate($request, $id) {
        $store = Store::withoutGlobalScope('os')->ass()->find($id);
        $currentDocuments = $store->documents ?? [];
        $currentTitles = $this->getDocumentTitleMap($store);

        if ($request->has('remove_documents')) {
            $toRemove = $request->remove_documents;
            $currentDocuments = array_diff($currentDocuments, $toRemove);
            foreach ($toRemove as $removed) {
                unset($currentTitles[$removed]);
            }
        }

        $existingTitlesInput = $request->input('existing_document_titles', []);
        foreach ($currentDocuments as $doc) {
            if (array_key_exists($doc, $existingTitlesInput)) {
                $currentTitles[$doc] = trim((string) $existingTitlesInput[$doc]);
            }
        }

        $newDocuments = $this->uploadDocuments($request, 'documents', 'document_titles');
        if (!empty($newDocuments['documents'])) {
            $currentDocuments = array_merge($currentDocuments, $newDocuments['documents']);
        }
        if (!empty($newDocuments['document_titles'])) {
            $currentTitles = array_merge($currentTitles, $newDocuments['document_titles']);
        }

        $currentDocuments = !empty($currentDocuments) ? array_values($currentDocuments) : [];
        $finalTitles = [];
        foreach ($currentDocuments as $doc) {
            if (isset($currentTitles[$doc])) {
                $finalTitles[$doc] = $currentTitles[$doc];
            }
        }

        return [
            'documents' => !empty($currentDocuments) ? $currentDocuments : null,
            'document_titles' => !empty($finalTitles) ? $finalTitles : null,
        ];
    }

    private function getDocumentTitleMap(Store $store): array
    {
        $titles = is_array($store->document_titles) ? $store->document_titles : [];
        $documents = is_array($store->documents) ? $store->documents : [];
        foreach ($documents as $doc) {
            if (!isset($titles[$doc]) || trim((string) $titles[$doc]) === '') {
                $titles[$doc] = pathinfo($doc, PATHINFO_FILENAME);
            }
        }
        return $titles;
    }

    private function validateDocumentTitlesForCreate(Request $request): void
    {
        if (! $request->hasFile('documents')) {
            return;
        }

        $files = $request->file('documents', []);
        $titles = $request->input('document_titles', []);

        $this->validateDocumentTitleSet($files, $titles, [], []);
    }

    private function validateDocumentTitlesForUpdate(Request $request, Store $store): void
    {
        $currentDocuments = is_array($store->documents) ? $store->documents : [];
        $removeDocuments = $request->input('remove_documents', []);
        $remainingDocuments = array_values(array_diff($currentDocuments, $removeDocuments));

        $existingTitles = $this->getDocumentTitleMap($store);
        $existingTitlesInput = $request->input('existing_document_titles', []);
        $remainingTitles = [];
        foreach ($remainingDocuments as $doc) {
            $remainingTitles[] = trim((string) ($existingTitlesInput[$doc] ?? ($existingTitles[$doc] ?? '')));
        }

        $files = $request->file('documents', []);
        $newTitles = $request->input('document_titles', []);

        $this->validateDocumentTitleSet($files, $newTitles, $remainingDocuments, $remainingTitles);
    }

    private function validateDocumentTitleSet(array $newFiles, array $newTitles, array $existingDocs, array $existingTitles): void
    {
        $validator = Validator::make([], []);
        $normalized = [];

        foreach ($existingTitles as $idx => $title) {
            if ($existingDocs[$idx] && $title === '') {
                $validator->errors()->add("existing_document_titles.{$existingDocs[$idx]}", 'Document title is required.');
                continue;
            }
            $lowerExistingTitle = mb_strtolower($title);
            if (in_array($lowerExistingTitle, $normalized, true)) {
                $validator->errors()->add("existing_document_titles.{$existingDocs[$idx]}", 'Document title must be unique for this asset.');
            } else {
                $normalized[] = $lowerExistingTitle;
            }
        }

        if (!empty($newFiles)) {
            if (count($newTitles) !== count($newFiles)) {
                $validator->errors()->add('document_titles', 'Document title is required for each uploaded file.');
            }

            foreach ($newFiles as $idx => $file) {
                $title = trim((string) ($newTitles[$idx] ?? ''));
                if ($title === '') {
                    $validator->errors()->add("document_titles.{$idx}", 'Document title is required.');
                    continue;
                }
                $lowerTitle = mb_strtolower($title);
                if (in_array($lowerTitle, $normalized, true)) {
                    $validator->errors()->add("document_titles.{$idx}", 'Document title must be unique for this asset.');
                } else {
                    $normalized[] = $lowerTitle;
                }
            }
        }

        if ($validator->errors()->any()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    public function destroy(Store $stores, $id) {
        $stores = Store::withoutGlobalScope('os')->ass()->find($id);

        $stores->delete();
        return redirect()->route('assets.index')->with('success', 'Location deleted successfully');        
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
    
        if ($request->assets == 1) {
            $query = Store::withoutGlobalScope('os')->ass()
            ->whereHas('assetStatus', function ($innerBuilder) {
                $innerBuilder->where('type', 1);
            })->whereDoesntHave('subassets');
        } else {
            $query = Store::query();
        }

        if (!empty($request->exceptThis)) {
            $query->where('id', '!=', $request->exceptThis);
        }
    
        if (!empty($queryString)) {
            $query->where(function ($innerBuilder) use  ($queryString) {
                $innerBuilder->where('name', 'LIKE', "%{$queryString}%")
                ->orWhere('code', 'LIKE', "%{$queryString}%")
                ->orWhere('ucode', 'LIKE', "%{$queryString}%");
            });
        }

        if ($request->strict_stores == 1 && auth()->check()) {
            $currentUserStore = UserStore::where('user_id', auth()->user()->id)->pluck('store_id')->toArray();
            if (!empty($currentUserStore) && !auth()->user()->isAdmin()) {
                $query->whereIn('id', $currentUserStore);
            }
        }

        if (!auth()->user()->isAdmin() && $request->strict_stores != 1) {
            $query->where('dom_id', auth()->user()->id);
        }

        if ($request->has('for_ticket') && $request->for_ticket == 1 && auth()->check()) {
            $getCurrentUserRoles = auth()->user()->roles[0]->id ?? 0;

            if ($getCurrentUserRoles == Helper::$roles['store-phone']) {
                $query->where('code', auth()->user()->employee_id);
            } else if ($getCurrentUserRoles == Helper::$roles['divisional-operations-manager']) {
                $query->where('dom_id', auth()->user()->id);
            }
        }

        if ($request->has('unassigned_only') && $request->unassigned_only == 1) {
            $assignedAssetIds = LocationAsset::pluck('asset_id')->toArray();
            $query->whereNotIn('id', $assignedAssetIds);
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => "{$item->code} - $item->name"
            ];
        });

        if ($getAll && $page == 1 && auth()->user()->isAdmin()) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function stateLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
    
        $query = City::query();
    
        if (!empty($queryString)) {
            $query->where('city_state', 'LIKE', "%{$queryString}%");
        }

        $query = $query->groupBy('city_state');
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->city_state,
                'text' => $item->city_state
            ];
        });

        if ($getAll && $page == 1) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function cityLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $state = $request->state;
        $limit = 10;
        $getAll = $request->getall;
    
        $query = City::query();
    
        if (!empty($queryString)) {
            $query->where('city_name', 'LIKE', "%{$queryString}%");
        }
    
        if (!empty($state)) {
            if ($state !== 'all') {
                $query->where('city_state', $state);
            }
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->city_id,
                'text' => $item->city_name
            ];
        });

        if ($getAll && $page == 1) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function importStores(Request $request) {
        $file = $request->file('xlsx');
        $data = Excel::toArray(new LocationImport(),$file);
        $response = [];
        $successCount = $errorCount = 0;

        $expectedHeaders = [
            'STORE NAME',
            'TYPE',
            'CITY',
            'STATE',
            'DOM FIRST NAME',
            'DOM MIDDLE NAME',
            'DOM LAST NAME',
            'OPS MGR',
            'OPS HEAD',
            'MODEL',
            'DOM ID',
            'DOM MOBILE',
            'ADDRESS 1',
            'ADDRESS 2',
            'BLOCK',
            'STREET',
            'LANDMARK',
            'STORE MOBILE',
            'STORE WHATSAPP',
            'LATITUDE',
            'LONGITUDE',
            'LOCATION URL',
            'STORE OPENING TIME',
            'STORE CLOSING TIME',
            'OPERATION START TIME',
            'OPERATION END TIME',
            'STORE MAIL ID',
            'CATEGORY',
        ];

        \DB::beginTransaction();

        try {
            if (!empty($data) && isset($data[0])) {
                foreach ($data[0] as $key => $row) {
                    if ($key) {

                        $codeName = explode(' ', $row[0]);
                        $city = City::where(\DB::raw('LOWER(city_name)'), strtolower($row[2]))
                        ->where(\DB::raw('LOWER(city_state)'), strtolower($row[3]))
                        ->first();

                        if (isset($codeName[0]) && isset($codeName[1])) {

                            $toBeAdded = [];

                            if (!$city) {
                                $errorCount++;
                                $response[$key] = 'City or state is invalid at C' . ($key + 1);
                                continue;
                            }

                            if (StoreType::withoutGlobalScope('os')->ass()->where(\DB::raw('LOWER(name)'), strtolower($row[1]))->doesntExist()) {
                                $errorCount++;
                                $response[$key] = 'Store type is invalid at B' . ($key + 1);
                                continue;
                            }

                            if (ModelType::withoutGlobalScope('os')->ass()->where(\DB::raw('LOWER(name)'), strtolower($row[9]))->doesntExist()) {
                                $errorCount++;
                                $response[$key] = 'Store model type is invalid at J' . ($key + 1);
                                continue;
                            }

                            if (!empty($row[27]) && StoreCategory::withoutGlobalScope('os')->ass()->where( \DB::raw('LOWER(name)'), strtolower( $row[27] ) )->doesntExist() ) {
                                $errorCount++;
                                $response[$key] = 'Store category is invalid at AB' . ($key + 1);
                                continue;
                            }

                            $theCurrentDom = null;

                            if (empty($row[10])) {
                                $errorCount++;
                                $response[$key] = 'DOM does not exists at K' . ($key + 1);
                                continue;
                            } else {
                                $explodedDomString = explode('_', str_replace(' ', '', $row[10]));
                                if (isset($explodedDomString[0])) {
                                    $currentDom = User::withTrashed()->where('employee_id', $explodedDomString[0])->first();

                                    if ($currentDom) {
                                        if (!empty($row[4])) {
                                            $currentDom->name = $row[4];
                                        }

                                        if (!empty($row[5])) {
                                            $currentDom->middle_name = $row[5];
                                        }

                                        if (!empty($row[6])) {
                                            $currentDom->last_name = $row[6];
                                        }                                        

                                        if (!empty($row[11])) {
                                            if (User::withTrashed()->where('employee_id', '!=', $explodedDomString[0])->where('phone_number', $row[11])->exists()) {
                                                $errorCount++;
                                                $response[$key] = 'Use different phone number at L' . ($key + 1);
                                                continue;
                                            } else {
                                                $currentDom->phone_number = $row[11];
                                                $currentDom->password = $row[11];
                                            }
                                        } else {
                                            $errorCount++;
                                            $response[$key] = 'Phone number is required at L' . ($key + 1);
                                            continue;
                                        }

                                        $theCurrentDom = $currentDom->id;
                                        $currentDom->save();
                                    } else {
                                        $currentDom = new User();
                                        $currentDom->employee_id = $explodedDomString[0];

                                        if (!empty($row[4])) {
                                            $currentDom->name = $row[4];
                                        } else {
                                            $errorCount++;
                                            $response[$key] = 'First name is required E' . ($key + 1);
                                            continue;
                                        }

                                        if (!empty($row[5])) {
                                            $currentDom->middle_name = $row[5];
                                        }

                                        if (!empty($row[6])) {
                                            $currentDom->last_name = $row[6];
                                        }

                                        if (!empty($row[11])) {
                                            if (User::withTrashed()->where('phone_number', $row[11])->exists()) {
                                                $errorCount++;
                                                $response[$key] = 'Use different phone number at L' . ($key + 1);
                                                continue;
                                            } else {
                                                $currentDom->phone_number = $row[11];
                                                $currentDom->password = $row[11];
                                            }
                                        } else {
                                            $errorCount++;
                                            $response[$key] = 'Phone number is required at L' . ($key + 1);
                                            continue;
                                        }

                                        $currentDom->save();
                                        $theCurrentDom = $currentDom->id;
                                        $currentDom->syncRoles([Helper::$roles['divisional-operations-manager']]);

                                        $optDepartment = Department::where('name', 'Operations')->first();

                                        if ($optDepartment) {
                                            Designation::create([
                                                'user_id' => $currentDom->id,
                                                'type_id' => $optDepartment->id,
                                                'type' => 3
                                            ]);
                                        }
                                        
                                    }

                                } else {
                                    $errorCount++;
                                    $response[$key] = 'DOM does not exists at K' . ($key + 1);
                                    continue;
                                }
                            }                            
                            

                            if (isset($row[12])) {
                                $toBeAdded['address1'] = $row[12];
                            }

                            if (isset($row[13])) {
                                $toBeAdded['address2'] = $row[13];
                            }

                            if (isset($row[14])) {
                                $toBeAdded['block'] = $row[14];
                            }

                            if (isset($row[15])) {
                                $toBeAdded['street'] = $row[15];
                            }

                            if (isset($row[16])) {
                                $toBeAdded['landmark'] = $row[16];
                            }

                            if (isset($row[17])) {
                                $toBeAdded['mobile'] = $row[17];
                            }

                            if (isset($row[18])) {
                                $toBeAdded['whatsapp'] = $row[18];
                            }

                            if (isset($row[19])) {
                                $toBeAdded['latitude'] = $row[19];
                            }

                            if (isset($row[20])) {
                                $toBeAdded['longitude'] = $row[20];
                            }

                            if (isset($row[21])) {
                                $toBeAdded['location_url'] = $row[21];
                            }
                            
                            if (isset($row[22]) && !empty(trim($row[22]))) {
                                $cellValue = trim($row[22]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['open_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['open_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['open_time'] = '12:00 AM';
                                    }
                                }
                            } else {
                                $toBeAdded['open_time'] = '12:00 AM';
                            }

                            if (isset($row[23]) && !empty(trim($row[23]))) {
                                $cellValue = trim($row[23]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['close_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['close_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['close_time'] = '11:59 PM';
                                    }
                                }
                            } else {
                                $toBeAdded['close_time'] = '11:59 PM';
                            }

                            if (isset($row[24]) && !empty(trim($row[24]))) {
                                $cellValue = trim($row[24]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['ops_start_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['ops_start_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['ops_start_time'] = '12:00 AM';
                                    }
                                }
                            } else {
                                $toBeAdded['ops_start_time'] = '12:00 AM';
                            }
                            
                            if (isset($row[25]) && !empty(trim($row[25]))) {
                                $cellValue = trim($row[25]);

                                if (is_numeric($cellValue)) {
                                    $toBeAdded['ops_end_time'] = Date::excelToDateTimeObject(floatval($cellValue))->format('h:i A');
                                } else {
                                    $time = strtotime($cellValue);
                                    if ($time !== false) {
                                        $toBeAdded['ops_end_time'] = date('h:i A', $time);
                                    } else {
                                        $toBeAdded['ops_end_time'] = '11:59 PM';
                                    }
                                }
                            } else {
                                $toBeAdded['ops_end_time'] = '11:59 PM';
                            }

                            if (isset($row[26]) && !empty(trim($row[26]))) {
                                $toBeAdded['email'] = $row[26];
                            } else {
                                $toBeAdded['email'] = '';
                            }

                            $toBeAdded['code'] = $codeName[0];
                            $toBeAdded['name'] = implode(' ', array_splice($codeName, 1, count($codeName)));
                            $toBeAdded['store_type'] = StoreType::withoutGlobalScope('os')->ass()->firstWhere(\DB::raw('LOWER(name)'), strtolower($row[1]))->id ?? null;
                            $toBeAdded['model_type'] = ModelType::withoutGlobalScope('os')->ass()->firstWhere(\DB::raw('LOWER(name)'), strtolower($row[9]))->id ?? null;
                            $toBeAdded['store_category'] = StoreCategory::withoutGlobalScope('os')->ass()->firstWhere( \DB::raw( 'LOWER(name)' ), strtolower( $row[27] ) )->id ?? null;
                            $toBeAdded['city'] = $city->city_id ?? null;
                            $toBeAdded['dom_id'] = $theCurrentDom;
                            
                            Store::updateOrCreate([
                                'code' => $toBeAdded['code'],
                                'type' => 1
                            ], $toBeAdded);

                            $successCount++;
                            
                        } else {
                                $errorCount++;
                                $response[$key] = 'Valid store information does not exists at A' . ($key + 1);
                                continue;
                        }
                    } else {

                        if (count($row) !== count($expectedHeaders)) {
                            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                                'checklist_id' => null,
                                'type' => 2,
                                'file_name' => $file->getClientOriginalName(),
                                'success' => 0,
                                'error' => 0,
                                'status' => 2,
                                'response' => [
                                    'Uploaded file has an incorrect number of columns.'
                                ]
                            ], $file);

                            \DB::rollBack();
                            return response()->json(['status' => false, 'message' => 'Uploaded file has an incorrect number of columns.']);
                        }

                        if (!(
                               strtoupper($row[0])  == $expectedHeaders[0]
                            && strtoupper($row[1])  == $expectedHeaders[1]
                            && strtoupper($row[2])  == $expectedHeaders[2]
                            && strtoupper($row[3])  == $expectedHeaders[3]
                            && strtoupper($row[4])  == $expectedHeaders[4]
                            && strtoupper($row[5])  == $expectedHeaders[5]
                            && strtoupper($row[6])  == $expectedHeaders[6]
                            && strtoupper($row[7])  == $expectedHeaders[7]
                            && strtoupper($row[8])  == $expectedHeaders[8]
                            && strtoupper($row[9])  == $expectedHeaders[9]
                            && strtoupper($row[10]) == $expectedHeaders[10]
                            && strtoupper($row[11]) == $expectedHeaders[11]
                            && strtoupper($row[12]) == $expectedHeaders[12]
                            && strtoupper($row[13]) == $expectedHeaders[13]
                            && strtoupper($row[14]) == $expectedHeaders[14]
                            && strtoupper($row[15]) == $expectedHeaders[15]
                            && strtoupper($row[16]) == $expectedHeaders[16]
                            && strtoupper($row[17]) == $expectedHeaders[17]
                            && strtoupper($row[18]) == $expectedHeaders[18]
                            && strtoupper($row[19]) == $expectedHeaders[19]
                            && strtoupper($row[20]) == $expectedHeaders[20]
                            && strtoupper($row[21]) == $expectedHeaders[21]
                            && strtoupper($row[22]) == $expectedHeaders[22]
                            && strtoupper($row[23]) == $expectedHeaders[23]
                            && strtoupper($row[24]) == $expectedHeaders[24]
                            && strtoupper($row[25]) == $expectedHeaders[25]
                            && strtoupper($row[26]) == $expectedHeaders[26]
                            && strtoupper($row[27]) == $expectedHeaders[27]
                        )) {
                            
                            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                                'checklist_id' => null,
                                'type' => 2,
                                'file_name' => $file->getClientOriginalName(),
                                'success' => 0,
                                'error' => 0,
                                'status' => 2,
                                'response' => [
                                    'Uploaded file headers do not match the expected format.'
                                ]
                            ], $file);

                            \DB::rollBack();
                            return response()->json(['status' => false, 'message' => 'Files header are mismatching.']);
                        }
                    }
                }
            } else {
                \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                    'checklist_id' => null,
                    'type' => 2,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => 0,
                    'error' => 0,
                    'status' => 2,
                    'response' => [
                        'File is empty'
                    ]
                ], $file);

                \DB::rollBack();
                return response()->json(['status' => false, 'message' => 'File is empty.']);
            }

            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => $successCount,
                'error' => $errorCount,
                'status' => $successCount == 0 ? 2 : (
                    $errorCount > 0 ? 3 : 1
                ),
                'response' => $response,
                'leave_blank' => 0
            ], $file, true);

            \DB::commit();
            return response()->json(['status' => true, 'message' => 'Store list updated successfully.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error occured on importing the stores ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json(['status' => false, 'message' => 'Something went wrong!']);
        }
    }

    public function exportStores()
    {
        // NOTE: route name is `export-assets`, method name kept for compatibility.
        // Exports the same filtered assets shown on the Assets listing screen.
        $stores = Store::with(['dom', 'assetStatus', 'store'])
            ->withoutGlobalScope('os')->ass()
            ->when(!empty(request('filter_code')), function ($builder) {
                $builder->where('code', request('filter_code'));
            })
            ->when(!empty(request('filter_ucode')), function ($builder) {
                $builder->where('ucode', request('filter_ucode'));
            })
            ->when(!empty(request('filter_name')) && request('filter_name') != 'all', function ($builder) {
                $builder->where('name', 'LIKE', '%' . request('filter_name') . '%');
            })
            ->when(!empty(request('filter_location')) && request('filter_location') != 'all', function ($builder) {
                $builder->where('location', request('filter_location'));
            })
            ->when(!empty(request('filter_dom')) && request('filter_dom') != 'all', function ($builder) {
                $builder->where('dom_id', request('filter_dom'));
            })
            ->when(!empty(request('filter_status')) && request('filter_status') != 'all', function ($builder) {
                $builder->where('asset_status_id', request('filter_status'));
            })
            ->get();

        return Excel::download(new \App\Exports\AssetsExport($stores), 'assets.xlsx');
    }

    private function uploadFile($request, $key) {
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/assets-images', $filename);
            return $filename;
        }
        return null;
    }

    private function uploadFiles($request, $key) {
        $files = [];
        if ($request->hasFile($key)) {
            foreach ($request->file($key) as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/assets-images', $filename);
                $files[] = $filename;
            }
        }
        return !empty($files) ? $files : null;
    }

    private function handlePrimaryImageUpdate($request, $id) {
        $store = Store::withoutGlobalScope('os')->ass()->find($id);
        $currentImage = $store->primary_image;

        if ($request->hasFile('primary_image')) {
            // if ($currentImage && \Storage::exists('public/assets-images/' . $currentImage)) {
            //     \Storage::delete('public/assets-images/' . $currentImage);
            // }
            return $this->uploadFile($request, 'primary_image');
        }

        return $currentImage;
    }

    private function handleSecondaryImagesUpdate($request, $id) {
        $store = Store::withoutGlobalScope('os')->ass()->find($id);
        $currentImages = $store->secondary_images ?? [];

        if ($request->has('remove_secondary_images')) {
            $toRemove = $request->remove_secondary_images;
            $currentImages = array_diff($currentImages, $toRemove);
            // foreach ($toRemove as $img) {
            //      if (\Storage::exists('public/assets-images/' . $img)) {
            //         \Storage::delete('public/assets-images/' . $img);
            //     }
            // }
        }

        $newImages = $this->uploadFiles($request, 'secondary_images');
        if ($newImages) {
            $currentImages = array_merge($currentImages, $newImages);
        }

        return !empty($currentImages) ? array_values($currentImages) : null;
    }
}