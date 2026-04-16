<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Menu\Concerns\HandlesAjaxValidation;
use App\Models\MenuOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    use HandlesAjaxValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'table_number' => ['required', 'integer', 'min:1'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:20'],
            'customer_email' => ['nullable', 'email:rfc,dns', 'max:191'],
            'status' => ['required', Rule::in(MenuOrder::STATUSES)],
            'payment_method' => ['required', 'string', 'max:30'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:menu_products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.addons_json' => ['nullable', 'string'],
            'items.*.attributes_json' => ['nullable', 'string'],
        ];
    }
}
