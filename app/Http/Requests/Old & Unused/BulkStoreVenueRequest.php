<?php

// namespace App\Http\Requests\V1;

// use Illuminate\Foundation\Http\FormRequest;

// class BulkStoreVenueRequest extends FormRequest
// {
//     /**
//      * Determine if the user is authorized to make this request.
//      *
//      * @return bool
//      */
//     public function authorize()
//     {
//         $user = $this->user();

//         return $user != null && $user->tokenCan('create');
//     }

//     /**
//      * Get the validation rules that apply to the request.
//      *
//      * @return array<string, mixed>
//      */
//     public function rules()
//     {
//         return [
//             '*.name' => ['required'],
//             '*.address' => ['required'],
//             '*.ownerId' => ['required', 'exists:users,id'],
//             '*.imageUrl' => ['required'],
//         ];
//     }

//     protected function prepareForValidation()
//     {
//         $data = [];
//         foreach($this->toArray() as $obj) {
//             $obj['owner_id'] = $obj['ownerId'] ?? null;
//             $obj['image_url'] = $obj['imageUrl'] ?? null;

//             $data[] = $obj;
//         }

//         $this->merge($data);
//     }
// }
