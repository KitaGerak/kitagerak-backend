<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user != null && $user->tokenCan('update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $method = $this->method();

        if ($method == 'PUT') {
            return [
                'name' => ['required'],
                'address' => ['required'],
                'address.street' => ['required'],
                'address.city' => ['required'],
                'address.province' => ['required'],
                'address.postalCode' => ['required'],
                'address.longitude' => ['required'],
                'address.latitude' => ['required'],
                'ownerId' => ['required', 'exists:users,id'],
                'imageUrl' => ['required'],
            ];
        } else {
            return [
                'name' => ['sometimes','required'],
                'address' => ['sometimes', 'required'],
                'address.street' => ['sometimes', 'required'],
                'address.city' => ['sometimes', 'required'],
                'address.province' => ['sometimes', 'required'],
                'address.postalCode' => ['sometimes', 'required'],
                'address.longitude' => ['sometimes', 'required'],
                'address.latitude' => ['sometimes', 'required'],
                'ownerId' => ['sometimes','required', 'exists:users,id'],
                'imageUrl' => ['sometimes','required'],
                'status' => ['sometimes','required'],
            ];
        }
    }

    protected function prepareForValidation()
    {
        if ($this->ownerId) {
            $this->merge([
                'owner_id' => $this->ownerId,
            ]);
        }

        if ($this->imageUrl) {
            $this->merge([
                'image_url' => $this->imageUrl,
            ]);
        }
        
    }
}
