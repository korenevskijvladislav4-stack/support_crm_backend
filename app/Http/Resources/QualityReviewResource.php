<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QualityReviewResource extends JsonResource
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
            'review'=>$this->review,
            'review_type' => $this->review_type,
            'total_score' => $this->total_score,
            'deductions' => ReviewDeductionResource::collection($this->whenLoaded('deductions'))
        ];
    }
}
