<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Update User Info',
    description: 'Update User Info'
)]

class ProfileUpdateRequest extends FormRequest
{
         #[OA\Property(type: 'string')]
        public string $f_name;

        #[OA\Property(type: 'string')]
        public string $l_name;

        #[OA\Property(type: 'string', format: 'email')]
        public string $email;


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$this->user()->id,
        ];
    }
}
