<?php

namespace App\Http\Requests\Attempt;

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
            'email' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'created_at' => 'sometimes|date',
            'is_viewed' => ['sometimes', 'nullable', function ($attribute, $value, $fail) {
                if ($value !== null && !in_array($value, ['true', 'false', '0', '1', true, false, 0, 1], true)) {
                    $fail('The ' . $attribute . ' field must be true or false.');
                }
            }],
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_field' => 'sometimes|string',
            'sort_direction' => 'sometimes|string|in:asc,desc',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Преобразуем строковые значения is_viewed в boolean
        if ($this->has('is_viewed')) {
            $value = $this->input('is_viewed');
            if ($value === null || $value === '') {
                // Не добавляем в запрос, если null или пустая строка
                $this->offsetUnset('is_viewed');
            } elseif (in_array($value, ['true', '1', 1, true], true)) {
                $this->merge(['is_viewed' => true]);
            } elseif (in_array($value, ['false', '0', 0, false], true)) {
                $this->merge(['is_viewed' => false]);
            }
        }
    }
}

