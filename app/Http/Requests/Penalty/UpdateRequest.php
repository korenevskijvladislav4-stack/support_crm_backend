<?php

namespace App\Http\Requests\Penalty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'user_id' => 'sometimes|exists:users,id',
            'hours_to_deduct' => 'sometimes|integer|min:1|max:1000',
            'comment' => 'sometimes|string|max:1000',
            'status' => 'sometimes|in:pending,approved,rejected',
        ];
    }
}
