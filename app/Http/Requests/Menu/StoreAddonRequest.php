<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Menu\Concerns\HandlesAjaxValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddonRequest extends FormRequest
{
    use HandlesAjaxValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Backward compatible single create
            'name' => ['nullable', 'string', 'max:191'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],

            // Multi-row create support
            'addons' => ['nullable', 'array'],
            'addons.*.name' => ['required_with:addons', 'string', 'max:191'],
            'addons.*.price' => ['required_with:addons', 'numeric', 'min:0'],
            'addons.*.description' => ['nullable', 'string'],
            'addons.*.status' => ['required_with:addons', 'boolean'],
        ];
    }
}
