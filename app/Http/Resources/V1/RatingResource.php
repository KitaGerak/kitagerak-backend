<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\RatingPhotoResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'review' => $this->review,
            'user' => new UserResource($this->user),
            'court' => $this->court->name,
            'courtPhotoUrl' => isset($this->court->images[0]->url) ? $this->court->images[0]->url : "",
            'ratingPhotos' => RatingPhotoResource::collection($this->ratingPhotos),
        ];
    }
}
