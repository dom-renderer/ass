<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Menu\Concerns\HandlesAjaxValidation;
use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromotionRequest extends FormRequest
{
    use HandlesAjaxValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $promotion = $this->route('promotion');
        $id = is_object($promotion) ? $promotion->id : $promotion;

        return [
            'name' => ['required', 'string', 'max:191'],
            'code' => ['required', 'string', 'max:50', Rule::unique('promotions', 'code')->ignore($id)],
            'type' => ['required', Rule::in(Promotion::TYPES)],
            'description' => ['nullable', 'string'],
            'is_auto_apply' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_stackable' => ['nullable', 'boolean'],
            'is_global' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_cart_amount' => ['nullable', 'numeric', 'min:0'],
            'total_usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'buy_product_id' => ['nullable', 'exists:menu_products,id'],
            'buy_quantity' => ['nullable', 'integer', 'min:1'],
            'get_product_id' => ['nullable', 'exists:menu_products,id'],
            'get_quantity' => ['nullable', 'integer', 'min:1'],
            'get_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'applicable_category_ids' => ['nullable', 'array'],
            'applicable_category_ids.*' => ['exists:menu_categories,id'],
            'applicable_product_ids' => ['nullable', 'array'],
            'applicable_product_ids.*' => ['exists:menu_products,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['exists:stores,id'],
            'rule_builder' => ['nullable', 'string'],
        ];
    }
}
