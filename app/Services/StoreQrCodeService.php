<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreQrCode;

class StoreQrCodeService
{
    /**
     * Regenerate QR rows for the store based on number_of_tables and location code.
     */
    public function syncForStore(Store $store): void
    {
        $code = (string) $store->code;
        $n = max(0, (int) $store->number_of_tables);

        StoreQrCode::withTrashed()->where('store_id', $store->id)->forceDelete();

        for ($t = 1; $t <= $n; $t++) {
            $label = 'L' . $code . '-' . str_pad((string) $t, 4, '0', STR_PAD_LEFT);

            StoreQrCode::create([
                'store_id' => $store->id,
                'table_number' => $t,
                'qr_label' => $label,
                'qr_url' => $label,
            ]);
        }
    }
}
