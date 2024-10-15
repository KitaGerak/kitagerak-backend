<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user !== null && $user->tokenCan('make_transaction');
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
                'rating' => ['required'],
                'review' => ['required'],
                'courtId' => ['required', 'exists:courts,id'],
                'userId' => ['required', 'exists:users,id'],
            ];
        } else {
            return [
                'rating' => ['required'],
                'review' => ['required'],
                'courtId' => ['required', 'exists:courts,id'],
                'userId' => ['required', 'exists:users,id'],
            ];
        }
        
    }

    protected function prepareForValidation()
    {
        if ($this->yearId) {
            $this->merge([
                'year_id' => $this->yearId,
            ]);
        }

        if ($this->createdBy) {
            $this->merge([
                'created_by' => $this->createdBy,
            ]);
        }
        
    }
        
}
