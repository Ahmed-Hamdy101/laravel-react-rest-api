<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product' => $this->product,
            'quantity' => $this->quantity,
            'price'    => $this->price,

        ];
    }
}
