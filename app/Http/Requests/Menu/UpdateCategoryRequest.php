<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Menu\Concerns\HandlesAjaxValidation;
use App\Models\MenuCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    use HandlesAjaxValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $category = $this->route('category');
        $id = $category instanceof MenuCategory ? $category->id : $category;

        return [
            'name' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('menu_categories', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', 'boolean'],
            'ordering' => ['required', 'integer', 'min:0'],
        ];
    }
}
