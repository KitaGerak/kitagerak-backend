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
                'courtId' => ['required', 'exists:courts,id'],
                'date' => ['required'],
                'timeStart' => ['required'],
                'timeFinish' => ['required'],
                'interval' => ['required'],
                'availability' => ['required'],
            ];
        } else {
            return [
                'courtId' => ['sometimes', 'required', 'exists:courts,id'],
                'date' => ['sometimes', 'required'],
                'timeStart' => ['sometimes', 'required'],
                'timeFinish' => ['sometimes', 'required'],
                'interval' => ['sometimes', 'required'],
                'availability' => ['sometimes', 'required'],
            ];
        }
        
    }

    protected function prepareForValidation()
    {
        if ($this->courtId) {
            $this->merge([
                'court_id' => $this->courtId,
            ]);
        }

        if ($this->timeStart) {
            $this->merge([
                'time_start' => $this->timeStart,
            ]);
        }

        if ($this->timeFinish) {
            $this->merge([
                'time_finish' => $this->timeFinish,
            ]);
        }
    }

}
