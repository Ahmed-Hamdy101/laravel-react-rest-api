<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Update User Request',
    description: 'Request body for updating a user'
)]

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
        #[OA\Property(type: 'string')]
        public string $f_name;

        #[OA\Property(type: 'string')]
        public string $l_name;

        #[OA\Property(type: 'string', format: 'email')]
        public string $email;

        #[OA\Property(
            type: 'string',
            format: 'password',
            writeOnly: true
        )]
        public string $current_password;

        #[OA\Property(
            type: 'string',
            format: 'password',
            writeOnly: true
        )]
        public string $password;

        #[OA\Property(
            type: 'string',
            format: 'password',
            writeOnly: true
        )]
        public string $password_confirmation;

        #[OA\Property(type: 'integer')]
        public int $role_id;

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
        $userId = $this->user()?->id;

        return [
            'f_name' => [
                'sometimes', 'string', 'max:255'
            ],
            'l_name' => [
                'sometimes', 'string', 'max:255'
            ],
            'email'  => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],          
            'current_password' => [
                'required_with:password',
                'current_password',
            ],

            'password' => [
                'sometimes',
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'role_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
        ];
    }
}