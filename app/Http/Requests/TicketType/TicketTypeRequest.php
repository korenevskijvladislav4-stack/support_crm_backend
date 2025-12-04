<?php

namespace App\Http\Requests\TicketType;

use Illuminate\Foundation\Http\FormRequest;

class TicketTypeRequest extends FormRequest
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
            'name' => 'required|string|max:100|unique:ticket_types,name,' . $this->route('ticket_type'),
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
