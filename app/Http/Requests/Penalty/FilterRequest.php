<?php

namespace App\Http\Requests\Penalty;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => 'sometimes|string',
            'user_id' => 'sometimes|integer|exists:users,id',
            'group_id' => 'sometimes|integer|exists:groups,id',
            'created_by' => 'sometimes|integer|exists:users,id',
            'created_at' => 'sometimes|date',
            'status' => 'sometimes|string|in:all,pending,approved,rejected',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_field' => 'sometimes|string',
            'sort_direction' => 'sometimes|string|in:asc,desc',
        ];
    }
}

