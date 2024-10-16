<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
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
        if ($this->method() == 'PUT') {
            return [
                'regularPrice' => ['required'],
                'memberPrice' => ['required'],
            ];
        } else {
            return [
                'regularPrice' => ['sometimes', 'required'],
                'memberPrice' => ['sometimes', 'required'],
            ];
        }
    }

    protected function prepareForValidation()
    {
        if ($this->regularPrice) {
            $this->merge([
                'regular_price' => $this->regularPrice,
            ]);
        }

        if ($this->memberPrice) {
            $this->merge([
                'member_price' => $this->memberPrice,
            ]);
        }
        
    }
}
