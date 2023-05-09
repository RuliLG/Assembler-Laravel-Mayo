<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $unique = $this->category ? 'unique:categories,name,' . $this->category->id : 'unique:categories,name';
        return [
            'name' => ['required', 'string', $unique],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
