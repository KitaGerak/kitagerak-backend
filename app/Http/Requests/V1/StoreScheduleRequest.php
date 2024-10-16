<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
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
            'courtId' => ['required'],
            'date' => ['required', 'date'],
            'timeStart' => ['required', 'date_format:H:i'],
            'timeFinish' => ['required', 'date_format:H:i'],
            'interval' => ['required'],
            'regularPrice' => ['required'],
            'memberPrice' => ['required']
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'court_id' => $this->courtId,
        ]);
        $this->merge([
            'time_start' => $this->timeStart,
        ]);
        $this->merge([
            'time_finish' => $this->timeFinish,
        ]);
        $this->merge([
            'regular_price' => $this->regularPrice,
        ]);
        $this->merge([
            'member_price' => $this->memberPrice,
        ]);
    }
}
