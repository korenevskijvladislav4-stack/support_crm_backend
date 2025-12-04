<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Models\TicketStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketStatusController extends ApiController
{
    public function index(): JsonResponse
    {
        $statuses = TicketStatus::orderBy('order_index')->get();
        return $this->success($statuses);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'order_index' => 'nullable|integer',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $status = TicketStatus::create($request->all());

        return $this->success($status, 'Ticket status created successfully', 201);
    }

    public function update(Request $request, TicketStatus $ticketStatus): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:50',
            'order_index' => 'nullable|integer',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $ticketStatus->update($request->all());

        return $this->success($ticketStatus, 'Ticket status updated successfully');
    }

    public function destroy(TicketStatus $ticketStatus): JsonResponse
    {
        $ticketStatus->delete();
        return $this->success(null, 'Ticket status deleted successfully');
    }
}
