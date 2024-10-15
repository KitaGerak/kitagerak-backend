<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user != null;
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
                'name' => ['required'],
                'phoneNumber' => ['required'],
                'image' => ['required'],
            ];
        } else if ($method == 'PATCH') {
            return [
                'name' => ['sometimes', 'required'],
                'phoneNumber' => ['sometimes', 'required'],
                'image' => ['sometimes', 'required'],
            ];
        }
    }

    protected function prepareForValidation()
    {
        if ($this->phoneNumber) {
            $this->merge([
                'phone_number' => $this->phoneNumber,
            ]);
        }
    }
}
