<?php

namespace App\Http\Resources\V1;

use App\Models\CourtImage;
use App\Models\CourtType;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

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
            'courtType' => new CourtTypeResource($this->courtType),
            'alternateType' => $this->alternate_type,
            'size' => $this->size,
            'price' => $this->price,
            'images' => CourtImageResource::collection($this->images),
            'status' => $this->status,
            'rating' => [
                "totalNumberOfPeople" => $this->number_of_people,
                "totalRating" => $this->sum_rating,
            ],
            'ratings' => RatingResource::collection($this->ratings),
        ];
    }
}
