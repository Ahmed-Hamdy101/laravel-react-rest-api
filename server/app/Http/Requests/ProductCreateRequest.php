<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;


#[OA\Schema(
    title: 'Product',
    description: 'Product model',
 )]
 
class ProductCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    #[OA\Property(
            title: "title",
            type: 'string'
        )
    ]

    public string $title = '';


    #[OA\Property(
            title: "description",
            type: 'string'
        )
    ]

    public string $description;


    #[OA\Property(
            title: "image",
            type: 'string'
        )
    ]

    public string $image;

    
    #[OA\Property(
        title: 'price',
        type: 'number',
        format: 'float',
        minimum: 0
    )]
    public float $price;


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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}
