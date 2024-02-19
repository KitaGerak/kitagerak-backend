<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'externalId' => $this->external_id,
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'court' => new CourtResource($this->whenLoaded('court')),
            'reason' => $this->reason,
            'transactionStatus' => new TransactionStatusResource($this->whenLoaded('transactionStatus')),
        ];
    }
}
