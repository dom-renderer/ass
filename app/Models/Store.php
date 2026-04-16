<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['pi', 'si', 'assdoc', 'assdoc_meta', 'scan_links', 'expiry_date', 'days_to_expire', 'consider_near_expiry', 'should_show_expiry_label', 'warranty_expiry_date', 'warranty_days_to_expire', 'warranty_consider_near_expiry', 'warranty_should_show_expiry_label'];
    protected $hidden = ['document_titles'];

    protected $casts = [
        'secondary_images' => 'array',
        'documents' => 'array',
        'document_titles' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('os', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->where('type', 0);
        });

        static::saved(function ($model) {
            if (!empty($model->ucode) && ($model->wasRecentlyCreated || $model->wasChanged('ucode'))) {
                $model->generateLabels();
            }
        });
    }

    public function generateLabels()
    {
        try {
            $disk = Storage::disk('public');
            
            $directories = [
                'qr-codes', 
                'barcodes', 
                'qr-code-labels', 
                'barcode-labels'
            ];
            
            foreach ($directories as $dir) {
                if (!$disk->exists($dir)) {
                    $disk->makeDirectory($dir);
                }
            }

            $fileName = $this->ucode;
            $imageName = "{$fileName}.png";
            $pdfName = "{$fileName}.pdf";

            $qrImage = QrCode::format('png')->size(300)->margin(0)->generate($fileName);
            $disk->put("qr-codes/{$imageName}", $qrImage);

            $generator = new BarcodeGeneratorPNG();
            $barcodeImage = $generator->getBarcode($fileName, $generator::TYPE_CODE_128, 3, 100);
            $disk->put("barcodes/{$imageName}", $barcodeImage);

            $data = [
                'model' => $this,
                'qrPath' => storage_path("app/public/qr-codes/{$imageName}"),
                'barcodePath' => storage_path("app/public/barcodes/{$imageName}"),
            ];

            $qrPdf = Pdf::loadView('labels.pdf_layout', array_merge($data, ['type' => 'qr']))->setPaper([0, 0, 260, 420], 'landscape');
            $disk->put("qr-code-labels/{$pdfName}", $qrPdf->output());

            $barcodePdf = Pdf::loadView('labels.pdf_layout', array_merge($data, ['type' => 'barcode']))->setPaper([0, 0, 260, 420], 'landscape');
            $disk->put("barcode-labels/{$pdfName}", $barcodePdf->output());

            $this->saveQuietly();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ERROR WHILE GENERATING LABEL : " . $e->getMessage() . " ON LINE : " . $e->getLine());
        }
    }    

    public function designations() {
        return $this->hasMany(Designation::class, 'type_id')->where('type', 1);
    }

    public function thecity() {
        return $this->belongsTo(City::class, 'city', 'city_id');
    }

    public function dom() {
        return $this->belongsTo(User::class, 'dom_id');
    }

    public function storetype() {
        return $this->belongsTo(StoreType::class, 'store_type')->withoutGlobalScope('os');
    }

    public function modeltype() {
        return $this->belongsTo(ModelType::class, 'model_type')->withoutGlobalScope('os');
    }

    public function storecategory() {
        return $this->belongsTo(StoreCategory::class, 'store_category')->withoutGlobalScope('os');
    }

    public function assetStatus() {
        return $this->belongsTo(AssetStatus::class, 'asset_status_id');
    }

    public function store() {
        return $this->belongsTo(Store::class, 'location');
    }

    public function subassets() {
        return $this->belongsToMany( Store::class, 'location_assets', 'location_id', 'asset_id' )->withoutGlobalScope('os');
    }

    public function docs() {
        return $this->hasMany(DocumentUpload::class, 'location_id');
    }

    public function storeQrCodes() {
        return $this->hasMany(StoreQrCode::class, 'store_id');
    }

    public function storeMenuItems() {
        return $this->hasMany(StoreMenuItem::class, 'store_id');
    }

    public function storeMenuProductAttributes() {
        return $this->hasMany(StoreMenuProductAttribute::class, 'store_id');
    }

    public function storeMenuProductAddons() {
        return $this->hasMany(StoreMenuProductAddon::class, 'store_id');
    }

    public function promotions() {
        return $this->belongsToMany(Promotion::class, 'promotion_stores');
    }

    public function getPiAttribute() {
        if ( file_exists( public_path( "storage/assets-images/{$this->primary_image}" ) ) ) {
            return asset( "storage/assets-images/{$this->primary_image}" );
        }

        return '';
    }

    public function getSiAttribute() {
        $attachment_url_arr = array();
        $attachment_arr = !empty($this->secondary_images) ? $this->secondary_images : array();
        if ( !empty($attachment_arr) ) {
            foreach ( $attachment_arr as $attachment ) {
                if ( file_exists( public_path( "storage/assets-images/{$attachment}" ) ) ) {
                    $attachment_url_arr[] = asset( "storage/assets-images/{$attachment}" );
                }
            }
        }

        return $attachment_url_arr;
    }

    public function getAssdocAttribute() {
        $attachment_url_arr = array();
        $attachment_arr = !empty($this->documents) ? $this->documents : array();
        if ( !empty($attachment_arr) ) {
            foreach ( $attachment_arr as $attachment ) {
                if ( file_exists( public_path( "storage/asset-documents/{$attachment}" ) ) ) {
                    $attachment_url_arr[] = asset( "storage/asset-documents/{$attachment}" );
                }
            }
        }

        return $attachment_url_arr;
    }

    public function getAssdocMetaAttribute() {
        $documents = !empty($this->documents) ? $this->documents : [];
        $meta = [];
        foreach ($documents as $document) {
            if (file_exists(public_path("storage/asset-documents/{$document}"))) {
                $meta[] = [
                    'file' => $document,
                    'url' => asset("storage/asset-documents/{$document}"),
                    'title' => $this->getDocumentTitleByFile($document),
                ];
            }
        }
        return $meta;
    }

    public function getDocumentTitleByFile(string $filename): string
    {
        $titles = is_array($this->document_titles) ? $this->document_titles : [];
        $title = $titles[$filename] ?? '';
        return !empty($title) ? $title : pathinfo($filename, PATHINFO_FILENAME);
    }

    public function getScanLinksAttribute() {
        return [
            'qr_code' => asset("storage/barcodes/{$this->ucode}.png"),
            'barcode' => asset("storage/qr-codes/{$this->ucode}.png"),
            'qr_code_label' => asset("storage/qr-code-labels/{$this->ucode}.pdf"),
            'barcode_label' => asset("storage/barcode-labels/{$this->ucode}.pdf")
        ];
    }

    public function scopeLoc($query)
    {
        return $query->where('type', 0);
    }

    public function scopeAss($query)
    {
        return $query->where('type', '!=', 0);
    }


    public function getExpiryDateAttribute()
    {
        return $this->po_date 
            ? Carbon::parse($this->po_date)->addMonths($this->lifespan)
            : null;
    }

    public function getDaysToExpireAttribute()
    {
        return $this->expiry_date 
            ? now()->diffInDays($this->expiry_date, false)
            : null;
    }

    public function getConsiderNearExpiryAttribute()
    {
        return 60;
    }

    public function getShouldShowExpiryLabelAttribute()
    {
        if ($this->expiry_date) {
            $daysToExpire = $this->days_to_expire;
            $threshold = $this->consider_near_expiry;

            return $daysToExpire !== null && $daysToExpire <= $threshold;
        }

        return false;
    }



    public function getWarrantyExpiryDateAttribute()
    {
        return $this->po_date 
            ? Carbon::parse($this->po_date)->addMonths($this->warranty)
            : null;
    }

    public function getWarrantyDaysToExpireAttribute()
    {
        return $this->warranty_expiry_date 
            ? now()->diffInDays($this->warranty_expiry_date, false)
            : null;
    }

    public function getWarrantyConsiderNearExpiryAttribute()
    {
        return 60;
    }

    public function getWarrantyShouldShowExpiryLabelAttribute()
    {
        if ($this->warranty_expiry_date) {
            $daysToExpire = $this->warranty_days_to_expire;
            $threshold = $this->warranty_consider_near_expiry;
            return $daysToExpire !== null && $daysToExpire <= $threshold;
        }

        return false;
    }

}