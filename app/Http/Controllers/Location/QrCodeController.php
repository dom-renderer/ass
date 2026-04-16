<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreQrCode;
use Illuminate\Support\Facades\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use ZipArchive;

class QrCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:store_qr_codes.view')->only(['index']);
        $this->middleware('permission:store_qr_codes.download')->only(['download', 'downloadAll']);
    }

    public function index(Store $location)
    {
        $page_title = 'QR Codes — ' . $location->name;
        $qrCodes = StoreQrCode::where('store_id', $location->id)->orderBy('table_number')->get();

        return view('locations.qr-codes', compact('page_title', 'location', 'qrCodes'));
    }

    public function download(Store $location, StoreQrCode $qr)
    {
        abort_unless((int) $qr->store_id === (int) $location->id, 404);

        $png = QrCode::format('png')->size(512)->margin(1)->encoding('UTF-8')->generate($qr->qr_label);

        return Response::make($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $qr->qr_label . '.png"',
        ]);
    }

    public function downloadAll(Store $location)
    {
        $codes = StoreQrCode::where('store_id', $location->id)->orderBy('table_number')->get();

        if ($codes->isEmpty()) {
            return redirect()->route('locations.qr-codes.index', $location->id)->with('error', 'No QR codes to download.');
        }

        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zipPath = $dir . '/location-' . $location->id . '-qrs-' . uniqid('', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP archive.');
        }

        foreach ($codes as $qr) {
            $png = QrCode::format('png')->size(512)->margin(1)->encoding('UTF-8')->generate($qr->qr_label);
            $zip->addFromString($qr->qr_label . '.png', $png);
        }

        $zip->close();

        return response()->download($zipPath, 'location-' . $location->id . '-qr-codes.zip')->deleteFileAfterSend(true);
    }
}
