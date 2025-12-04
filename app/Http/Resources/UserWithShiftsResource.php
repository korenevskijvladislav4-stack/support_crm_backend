<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWithShiftsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Фильтруем смены, оставляя только те, у которых есть user_shift_id (не удаленные) и которые не отклонены
        $shifts = $this->whenLoaded('shifts');
        if ($shifts && $shifts instanceof \Illuminate\Database\Eloquent\Collection) {
            $shifts = $shifts->filter(function ($shift) {
                $hasId = isset($shift->pivot->id) && $shift->pivot->id !== null;
                $notRejected = ($shift->pivot->status ?? 'approved') !== \App\Models\UserShift::STATUS_REJECTED;
                return $hasId && $notRejected;
            })->values();
        }
        
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'surname'=>$this->surname,
            'email'=>$this->email,
            'roles'=>$this->roles->pluck('name'),
            'team'=>$this->team?->name,
            'group'=>$this->group?->name,
            'schedule_type'=>$this->scheduleType?->name,
            'shifts' => $shifts ? ShiftsResource::collection($shifts) : []
        ];
    }
}
