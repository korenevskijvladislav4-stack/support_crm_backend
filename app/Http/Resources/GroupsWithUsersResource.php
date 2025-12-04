<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupsWithUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'shift_type'=>$this->shift_type,
            'shift_number' => $this->shift_number,
            'team' => $this->team?->name,
            'team_id' => $this->team_id,
            'supervisor' => $this->supervisor ? [
                'id' => $this->supervisor->id,
                'name' => $this->supervisor->name,
                'surname' => $this->supervisor->surname,
                'fullname' => trim($this->supervisor->name . ' ' . ($this->supervisor->surname ?? ''))
            ] : null,
            'users'=> UserWithShiftsResource::collection($this->whenLoaded('users'))
        ];
    }
}
