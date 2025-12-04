<?php

namespace App\Http\Requests\ExtraShift;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'shift_id' => 'integer|required',
            'user_id' => 'integer|required',
            'role_id' => 'integer|required',
            'start_at' => 'required',
            'end_at' => 'required',
        ];
    }
}
