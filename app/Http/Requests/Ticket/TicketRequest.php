<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type_id' => 'required|exists:ticket_types,id',
            'status_id' => 'required|exists:ticket_statuses,id',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|integer|between:1,5',
            'due_date' => 'nullable|date|after:today',
        ];
    }
    public function messages():array
    {
        return [
            'title.required' => 'Заголовок обязателен для заполнения',
            'description.required' => 'Описание обязательно для заполнения',
            'type_id.required' => 'Тип тикета обязателен для выбора',
            'status_id.required' => 'Статус обязателен для выбора',
            'priority.required' => 'Приоритет обязателен для выбора',
        ];
    }
}
