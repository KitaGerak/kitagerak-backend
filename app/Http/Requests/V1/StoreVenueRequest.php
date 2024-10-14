<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user != null && $user->tokenCan('create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'description' => ['required'],
            'addressId' => ['exists:addresses,id'],

            // 'address' => ['required'],
            // 'address.street' => ['required'],
            // 'address.city' => ['required'],
            // 'address.province' => ['required'],
            // 'address.postalCode' => ['required'],
            // 'address.longitude' => ['required'],
            // 'address.latitude' => ['required'],

            'facilitiesId' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'address_id' => $this->addressId,
        ]);
    }
}
