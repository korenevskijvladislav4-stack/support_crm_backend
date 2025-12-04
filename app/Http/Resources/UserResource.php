<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'surname'=>$this->surname,
            'email'=>$this->email,
            'phone'=>$this->phone,
            'roles'=>$this->whenLoaded('roles', fn() => $this->roles->pluck('name'), []),
            'team'=>$this->team?->name,
            'team_id'=>$this->team_id,
            'group'=>$this->group?->name,
            'schedule_type'=>$this->scheduleType?->name
        ];
    }
}
