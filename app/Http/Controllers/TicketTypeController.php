<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketTypeController extends ApiController
{
    public function index(): JsonResponse
    {
        $types = TicketType::where('is_active', true)->get();
        return $this->success($types);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|in:text,textarea,select,number,email,date',
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $type = TicketType::create($request->all());

        return $this->success($type, 'Ticket type created successfully', 201);
    }

    public function show(TicketType $ticketType): JsonResponse
    {
        return $this->success($ticketType);
    }

    public function update(Request $request, TicketType $ticketType): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'sometimes|required|array',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|in:text,textarea,select,number,email,date',
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $ticketType->update($request->all());

        return $this->success($ticketType, 'Ticket type updated successfully');
    }

    public function destroy(TicketType $ticketType): JsonResponse
    {
        $ticketType->update(['is_active' => false]);
        return $this->success(null, 'Ticket type deleted successfully');
    }
}
