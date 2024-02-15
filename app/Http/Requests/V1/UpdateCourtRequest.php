<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourtRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user !== null && $user->tokenCan('update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if ($this->method() == 'PUT') {
            return [
                'name' => ['required'],
                'venueId' => ['required','exists:venues,id'],
                'floorType' => ['required'],
                'courtTypeId' => ['required', 'exists:court_types,id'],
                // 'alternateType' => ['string'],
                'price' => ['required', 'integer'],
            ];
        } else {
            return [
                'name' => ['sometimes','required'],
                'venueId' => ['sometimes','required','exists:venues,id'],
                'floorType' => ['sometimes','required'],
                'courtTypeId' => ['sometimes','required', 'exists:court_types,id'],
                // 'alternateType' => ['sometimes','string'],
                'price' => ['sometimes','required', 'integer'],
            ];
        }
        
    }

    protected function prepareForValidation()
    {
        if ($this->venueId) {
            $this->merge([
                'venue_id' => $this->venueId,
            ]);
        }

        if ($this->floorType) {
            $this->merge([
                'floor_type' => $this->floorType,
            ]);
        }

        if ($this->courtTypeId) {
            $this->merge([
                'court_type_id' => $this->courtTypeId,
            ]);
        }

        if ($this->alternateType) {
            $this->merge([
                'alternate_type' => $this->alternateType,
            ]);
        }
        
    }
}
