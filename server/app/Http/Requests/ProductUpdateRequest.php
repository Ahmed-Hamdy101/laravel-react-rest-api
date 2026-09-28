<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Product',
    description: 'Product model update request',
 )]
 

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|nullable|string',
        ];
    }
}
