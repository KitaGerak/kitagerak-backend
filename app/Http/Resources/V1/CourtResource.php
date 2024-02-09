<?php

namespace App\Http\Resources\V1;

use App\Models\CourtImage;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'venueId' => $this->venue_id,
            'floorType' => $this->floor_type,
            'courtTypeId' => $this->court_type_id,
            'alternateType' => $this->alternate_type,
            'price' => $this->price,
            'images' => CourtImageResource::collection($this->images),
            'status' => $this->status,
        ];
    }
}
