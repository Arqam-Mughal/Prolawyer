<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorklistResource extends JsonResource
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
            'title' => $this->title,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'case_id' => $this->case_id,
            'repeated_options' => $this->repeated_options,
            'start_date' => $this->start_date,
            'set_time' => $this->set_time,
            'end_option' => $this->end_option,
            'weekdays' => $this->weekdays,
            'end_date' => $this->end_date,
            'end_occurrences' => $this->end_occurrences,
            'passed_occurrences' => $this->passed_occurrences,
            'last_occurred' => $this->last_occurred,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
