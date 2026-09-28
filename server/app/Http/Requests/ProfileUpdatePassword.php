<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Update User Password',
    description: 'Update User Password'
)]

class ProfileUpdatePassword extends FormRequest
{
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
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
    }
}
