<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Проверяем, что pivot существует и имеет id
        $userShiftId = null;
        if (isset($this->pivot)) {
            // Пробуем получить id разными способами
            if (isset($this->pivot->id)) {
                $userShiftId = $this->pivot->id;
            } elseif (is_object($this->pivot) && property_exists($this->pivot, 'id')) {
                $userShiftId = $this->pivot->id;
            } elseif (is_array($this->pivot) && isset($this->pivot['id'])) {
                $userShiftId = $this->pivot['id'];
            }
        }
        
        // Если user_shift_id все еще null, пытаемся найти запись в БД
        if ($userShiftId === null && isset($this->pivot->user_id) && isset($this->pivot->shift_id)) {
            $userShift = \App\Models\UserShift::where('user_id', $this->pivot->user_id)
                ->where('shift_id', $this->pivot->shift_id)
                ->first();
            if ($userShift) {
                $userShiftId = $userShift->id;
            }
        }
        
        return [
            'id' => $this->id,
            'user_shift_id' => $userShiftId, // ID записи в user_shifts
            'duration' => $this->pivot->duration ?? 12,
            'is_active' => $this->pivot->is_active ?? true,
            'status' => $this->pivot->status ?? 'approved',
            'date' => $this->date
        ];
    }
}
