<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreScheduleRequest extends FormRequest
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
            '*.courtId' => ['required', 'exists:courts,id'],
            '*.date' => ['required'],
            '*.timeStart' => ['required'],
            '*.timeFinish' => ['required'],
            '*.interval' => ['required'],
            '*.availability' => ['required'],
            '*.price' => ['integer'],
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];
        foreach($this->toArray() as $obj) {
            $obj['court_id'] = $obj['courtId'] ?? null;
            $obj['time_start'] = $obj['timeStart'] ?? null;
            $obj['time_finish'] = $obj['timeFinish'] ?? null;

            $data[] = $obj;
        }

        $this->merge($data);
    }
}
