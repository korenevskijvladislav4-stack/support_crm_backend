<?php

namespace App\Http\Controllers;


use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TicketCommentController extends ApiController
{
    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        return DB::transaction(function () use ($request, $ticket) {
            $comment = TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'content' => $request->content,
                'is_internal' => $request->is_internal ?? false,
            ]);

            // Запись активности
            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'type' => 'comment_added',
                'description' => 'Добавлен комментарий',
            ]);

            return $this->success($comment->load('user'), 'Comment added successfully', 201);
        });
    }

    public function update(Request $request, TicketComment $comment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $comment->update(['content' => $request->content]);

        return $this->success($comment->load('user'), 'Comment updated successfully');
    }

    public function destroy(TicketComment $comment): JsonResponse
    {
        $comment->delete();
        return $this->success(null, 'Comment deleted successfully');
    }
}
