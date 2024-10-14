<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourtRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user !== null && $user->tokenCan('create');
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
            'venueId' => ['required', 'exists:venues,id'],
            'floorType' => ['required'],
            'courtTypeId' => ['required', 'exists:court_types,id'],
            'alternateType' => ['string'],
            'size' => ['double'],
            'regularPrice' => ['required'],
            'memberPrice' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'venue_id' => $this->venueId,
            'floor_type' => $this->floorType,
            'court_type_id' => $this->courtTypeId,
            'alternate_type' => $this->alternateType,
            'regular_price' => $this->regularPrice,
            'member_price' => $this->memberPrice,
        ]);
    }

}
