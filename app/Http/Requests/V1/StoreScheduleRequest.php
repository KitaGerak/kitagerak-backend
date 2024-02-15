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
            'courtId' => ['required', 'exists:courts,id'],
            'date' => ['required'],
            'timeStart' => ['required'],
            'timeFinish' => ['required'],
            'interval' => ['required'],
            'availability' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'court_id' => $this->courtId,
            'time_start' => $this->timeStart,
            'time_finish' => $this->timeFinish,
        ]);
    }

}
