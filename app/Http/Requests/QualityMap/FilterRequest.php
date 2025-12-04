<?php

namespace App\Http\Requests\QualityMap;

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
            'team_id' => 'sometimes|integer|exists:teams,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'group_id' => 'sometimes|integer|exists:groups,id',
            'checker_id' => 'sometimes|integer|exists:users,id',
            'start_date' => 'sometimes|date',
            'status' => 'sometimes|string|in:all,active,completed',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_field' => 'sometimes|string',
            'sort_direction' => 'sometimes|string|in:asc,desc',
        ];
    }
}

