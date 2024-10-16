<?php

namespace App\Http\Resources\V1;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'venue' => new VenueResource($this->whenLoaded('venue')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'floorType' => $this->floor_type,
            'courtType' => new CourtTypeResource($this->whenLoaded('courtType')),
            'alternateType' => $this->alternate_type,
            'size' => $this->size,
            'prices' => [
                $this->regular_price,
                $this->member_price,
            ],
            'images' => CourtImageResource::collection($this->whenLoaded('images')),
            'status' => $this->status,
            'rating' => [
                "totalNumberOfPeople" => $this->number_of_people,
                "totalRating" => $this->sum_rating,
            ],
            'ratings' => RatingResource::collection($this->whenLoaded('ratings')),
        ];
    }
}
