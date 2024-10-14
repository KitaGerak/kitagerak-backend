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

        if ($method == 'POST') {
            return [
                'description' => ['required'],
                'status' => ['required'],
                'ownerId' => ['required'],
            ];
        } else if ($method == 'PATCH') {
            return [
                'description' => ['sometimes', 'required'],
                'status' => ['sometimes','required'],
                'ownerId' => ['sometimes', 'required'],
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
    }
}
