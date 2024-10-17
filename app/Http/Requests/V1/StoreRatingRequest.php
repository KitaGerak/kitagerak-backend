<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // $user = $this->user();

        // return $user !== null && $user->tokenCan('make_transaction');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'rating' => ['required'],
            'review' => ['required'],
            'courtId' => ['required', 'exists:courts,id'],
            'userId' => ['required', 'exists:users,id'],
            'transactionId' => ['required', 'exists:transactions,external_id']
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'court_id' => $this->courtId,
            'user_id' => $this->userId,
            'transaction_id' => $this->transactionId
        ]);
    }
}
