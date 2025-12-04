<?php

namespace App\Http\Requests\QualityReview;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на обновление обзора качества
 */
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
            'review' => 'sometimes|required|string',
            'review_type' => 'sometimes|required|string',
            'deductions' => 'required|array',
            'deductions.*.id' => 'required|exists:quality_deductions,id',
            'deductions.*.points' => 'required|integer|min:0',
            'deductions.*.comments' => 'nullable|string',
        ];
    }
}

