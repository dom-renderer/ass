<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Menu\Concerns\HandlesAjaxValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttributeRequest extends FormRequest
{
    use HandlesAjaxValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'status' => ['required', 'boolean'],
            'values' => ['nullable', 'array'],
            'values.*.id' => ['nullable', 'integer', 'exists:menu_attribute_values,id'],
            'values.*.value' => ['required_with:values', 'string', 'max:191'],
            'values.*.extra_price' => ['nullable', 'numeric', 'min:0'],
            'values.*.ordering' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
