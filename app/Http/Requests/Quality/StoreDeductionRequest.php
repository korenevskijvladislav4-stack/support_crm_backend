<?php

namespace App\Http\Requests\Quality;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeductionRequest extends FormRequest
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
            'quality_map_id' => 'required|exists:quality_maps,id',
            'criteria_id' => 'required|exists:quality_criterias,id',
            'chat_id' => 'required|string',
            'deduction' => 'required|integer|min:0|max:100',
            'comment' => 'required|string|max:1000',
        ];
    }
}
