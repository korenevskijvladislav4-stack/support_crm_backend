<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс для смены пользователя
 */
class UserShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'shift_id' => $this->shift_id,
            'duration' => $this->duration,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_viewed' => $this->is_viewed,
            'shift' => $this->whenLoaded('shift', function () {
                return [
                    'id' => $this->shift->id,
                    'date' => $this->shift->date,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name . ' ' . $this->user->surname,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

