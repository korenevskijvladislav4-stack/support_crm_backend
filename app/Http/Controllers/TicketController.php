<?php

namespace App\Http\Controllers;


use App\Models\Ticket;
use App\Models\TicketActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TicketController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::with(['type', 'status', 'creator', 'team'])
            ->orderBy('created_at', 'desc');

        // Фильтрация
        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('type_id') && $request->type_id) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('creator_id') && $request->creator_id) {
            $query->where('creator_id', $request->creator_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate($request->get('per_page', 20));

        return $this->success($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'description' => 'required|string',
            'type_id' => 'required|exists:ticket_types,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'team_id' => 'nullable|exists:teams,id',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        return DB::transaction(function () use ($request) {
            $defaultStatus = \App\Models\TicketStatus::where('is_default', true)->first();

            $ticket = Ticket::create([
                'title' => $request->title,
                'description' => $request->description,
                'type_id' => $request->type_id,
                'status_id' => $defaultStatus->id,
                'priority' => $request->priority,
                'creator_id' => auth()->id(),
                'team_id' => $request->team_id,
                'custom_fields' => $request->custom_fields,
            ]);

            // Запись активности
            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'type' => 'ticket_created',
                'description' => 'Тикет создан',
            ]);

            return $this->success($ticket->load(['type', 'status', 'creator', 'team']), 'Ticket created successfully', 201);
        });
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load([
            'type',
            'status',
            'creator',
            'team',
            'comments.user',
            'attachments.user',
            'activities.user'
        ]);

        return $this->success($ticket);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:500',
            'description' => 'sometimes|required|string',
            'status_id' => 'sometimes|required|exists:ticket_statuses,id',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        return DB::transaction(function () use ($request, $ticket) {
            $oldData = $ticket->toArray();

            $ticket->update($request->all());

            // Запись активности при изменении статуса
            if ($request->has('status_id') && $oldData['status_id'] != $request->status_id) {
                $oldStatus = \App\Models\TicketStatus::find($oldData['status_id']);
                $newStatus = \App\Models\TicketStatus::find($request->status_id);

                TicketActivity::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'type' => 'status_changed',
                    'description' => "Статус изменен: {$oldStatus->name} → {$newStatus->name}",
                    'old_data' => ['status_id' => $oldData['status_id']],
                    'new_data' => ['status_id' => $request->status_id],
                ]);
            }

            return $this->success($ticket->load(['type', 'status', 'creator', 'team']), 'Ticket updated successfully');
        });
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status_id' => 'required|exists:ticket_statuses,id'
        ]);

        $oldStatus = $ticket->status;
        $ticket->update(['status_id' => $request->status_id]);
        $ticket->load('status');

        // Запись в активность
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'type' => 'status_changed',
            'description' => "Статус изменен с '{$oldStatus->name}' на '{$ticket->status->name}'",
        ]);

        return $this->success($ticket, 'Status updated successfully');
    }
}
