<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class VenueResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $rating = DB::select('SELECT SUM(number_of_people) AS totalNumberOfPeople, AVG(sum_rating) AS totalRating FROM `courts` GROUP BY venue_id HAVING venue_id = ?', [$this->id]);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => new AddressResource($this->address),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'imageUrl' => new VenueImageCollection($this->venueImages),
            'status' => $this->status,
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
            'rating' => $rating,
        ];
    }
}
