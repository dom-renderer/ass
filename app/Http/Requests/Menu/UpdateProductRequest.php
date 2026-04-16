<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Menu\Concerns\HandlesAjaxValidation;
use App\Models\MenuProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    use HandlesAjaxValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $product = $this->route('product');
        $id = $product instanceof MenuProduct ? $product->id : $product;

        return [
            'category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('menu_products', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', 'boolean'],
            'ordering' => ['required', 'integer', 'min:0'],
            'product_attributes' => ['nullable', 'array'],
            'product_attributes.*.attribute_id' => ['required_with:product_attributes', 'exists:menu_attributes,id'],
            'product_attributes.*.attribute_value_id' => ['required_with:product_attributes', 'exists:menu_attribute_values,id'],
            'product_attributes.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'product_attributes.*.is_available' => ['nullable', 'boolean'],
            'product_attributes.*.is_default' => ['nullable', 'boolean'],
            'product_addons' => ['nullable', 'array'],
            'product_addons.*.addon_id' => ['required_with:product_addons', 'exists:menu_addons,id'],
            'product_addons.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'product_addons.*.is_available' => ['nullable', 'boolean'],
            'product_addons.*.is_default' => ['nullable', 'boolean'],
        ];
    }
}
