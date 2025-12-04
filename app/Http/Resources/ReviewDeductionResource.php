<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewDeductionResource extends JsonResource
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
            'criteria' => $this->quality_criteria->name,
            'points' => $this->points,
            'comments' => $this->comments,
            'created_at' => $this->created_at        ];
    }
}
