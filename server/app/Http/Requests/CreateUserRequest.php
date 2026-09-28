<?php

namespace App\Http\Requests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Create New User ",
    description: "Create New Record",
)]

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    #[OA\Property(
        title: "f_name",
        type: "string"
    )]
    public string $f_name;

    #[OA\Property(
        title: "l_name",
        type: "string"
    )]
    public  string $l_name;

    #[OA\Property(
        title: "email",
        type: "string"
    )]
    public  string $email;
    
    #[OA\Property(
        title: "password",
        type: "string"
    )]

    public string $password;
    
    #[OA\Property(
        title: "role_id",
        type: "integer"
    )]

    public int $role_id;

    public function authorize(): bool
    {
        // req gates for edit  Users
        return Gate::allows('edit', \App\Models\User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'f_name'=> 'required|string|max:255',
            'l_name'=> 'required|string|max:255',
            'email'=> 'required|string|email|max:255|unique:users',
           'password'=> 'required|string|min:8',
            'role_id'=> 'required',
        ];
    }
}
