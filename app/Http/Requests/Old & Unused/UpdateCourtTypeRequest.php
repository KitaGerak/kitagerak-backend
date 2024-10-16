<?php

// namespace App\Http\Requests\V1;

// use Illuminate\Foundation\Http\FormRequest;

// class UpdateCourtTypeRequest extends FormRequest
// {
//     /**
//      * Determine if the user is authorized to make this request.
//      *
//      * @return bool
//      */
//     public function authorize()
//     {
//         $user = $this->user();

//         return $user !== null && $user->tokenCan('admin');
//     }

//     /**
//      * Get the validation rules that apply to the request.
//      *
//      * @return array<string, mixed>
//      */
//     public function rules()
//     {
//         if ($this->method() == 'PUT') {
//             return [
//                 'type' => ['required'],
//             ];
//         } else {
//             return [
//                 'type' => ['sometimes', 'required'],
//             ];
//         }
        
//     }
// }
