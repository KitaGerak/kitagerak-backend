<?php

// namespace App\Http\Requests\V1;

// use Illuminate\Foundation\Http\FormRequest;

// class UpdateAccountRequest extends FormRequest
// {
//     /**
//      * Determine if the user is authorized to make this request.
//      *
//      * @return bool
//      */
//     public function authorize()
//     {
//         $user = $this->user();

//         return $user != null;
//     }

//     /**
//      * Get the validation rules that apply to the request.
//      *
//      * @return array<string, mixed>
//      */
//     public function rules()
//     {
//         return [
//             'name' => ['sometimes','required'],
//             'email' => ['sometimes','required'],
//             'phoneNumber' => ['sometimes','required'],
//             'image' => ['sometimes','required'],
//         ];
        
//     }

//     protected function prepareForValidation()
//     {
//         if ($this->phoneNumber) {
//             $this->merge([
//                 'phone_number' => $this->phoneNumber,
//             ]);
//         }
        
//     }
// }
