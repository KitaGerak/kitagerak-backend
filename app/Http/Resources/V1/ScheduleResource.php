<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // 'courtId' => $this->court_id,
            'date' => $this->date,
            'timeStart' => $this->time_start,
            'timeFinish' => $this->time_finish,
            'interval' => $this->interval,
            'availability' => $this->availability,
            'status' => $this->status,
        ];
    }
}
