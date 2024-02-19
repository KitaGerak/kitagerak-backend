<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();

        return $user != null && $user->tokenCan('admin');
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
                'reason' => ['required'],
                'transactionStatusId' => ['required'],
                'scheduleId' => ['required'],
            ];
        } else {
            return [
                'reason' => ['sometimes', 'required'],
                'transactionStatusId' => ['sometimes', 'required'],
                'scheduleId' => ['sometimes', 'required'],
            ];
        }
        
    }

    protected function prepareForValidation()
    {
        if ($this->transactionStatusId) {
            $this->merge([
                'transaction_status_id' => $this->transactionStatusId,
            ]);
        }

        if ($this->scheduleId) {
            $this->merge([
                'schedule_id' => $this->scheduleId,
            ]);
        }
        
    }
}
