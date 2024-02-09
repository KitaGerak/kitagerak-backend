<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

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
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => new AddressResource($this->address),
            'ownerId' => $this->owner_id,
            'imageUrl' => $this->image_url,
            'status' => $this->status,
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
        ];
    }
}
