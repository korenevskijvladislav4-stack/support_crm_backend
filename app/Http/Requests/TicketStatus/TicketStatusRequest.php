<?php

namespace App\Http\Requests\TicketStatus;

use Illuminate\Foundation\Http\FormRequest;

class TicketStatusRequest extends FormRequest
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
            'name' => 'required|string|max:100|unique:ticket_statuses,name,' . $this->route('ticket_status'),
            'color' => 'required|string|max:7',
            'order' => 'required|integer',
            'is_default' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }
}
